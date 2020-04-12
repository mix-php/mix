<?php

namespace Mix\Grpc;

use Mix\Context\Context;
use Mix\Grpc\Exception\NotFoundException;
use Mix\Http\Message\Factory\StreamFactory;
use Mix\Http\Message\Request;
use Mix\Http\Message\Response;
use Mix\Http\Message\ServerRequest;
use Mix\Http\Server\Middleware\MiddlewareDispatcher;

/**
 * Class Server
 * @package Mix\Grpc
 */
class Server implements \Mix\Http\Server\HandlerInterface, \Mix\Server\HandlerInterface
{

    /**
     * @var string
     */
    public $host = '';

    /**
     * @var int
     */
    public $port = 0;

    /**
     * @var bool
     */
    public $ssl = false;

    /**
     * @var bool
     */
    public $reusePort = false;

    /**
     * 事件调度器
     * @var EventDispatcherInterface
     */
    public $dispatcher;

    /**
     * @var array MiddlewareInterface class or object
     */
    public $middleware = [];

    /**
     * @var \Mix\Http\Server\Server
     */
    protected $server;

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var array [[$service, $namespace, $suffix],...]
     */
    protected $services = [];

    /**
     * @var callable[]
     */
    protected $callables = [];

    /**
     * Server constructor.
     * @param string $host
     * @param int $port
     * @param bool $reusePort
     */
    public function __construct(string $host, int $port, bool $ssl = false, bool $reusePort = false)
    {
        $this->host      = $host;
        $this->port      = $port;
        $this->ssl       = $ssl;
        $this->reusePort = $reusePort;
    }

    /**
     * Set
     * @param array $options
     */
    public function set(array $options)
    {
        $this->options = $options;
    }

    /**
     * 获取全部 service 名称, 通过类名
     *
     * Class                                              Service           Method
     * [namespace]Foo[suffix]::Bar                        foo               Foo.Bar
     * [namespace]Foo/Bar[suffix]::Baz                    foo               Bar.Baz
     * [namespace]Foo/Bar/Baz[suffix]::Cat                foo.bar           Baz.Cat
     * [namespace]V1/Foo[suffix]::Bar                     v1.foo            Foo.Bar
     * [namespace]V1/Foo/Bar[suffix]::Baz                 v1.foo.bar        Bar.Baz
     * [namespace]V1/Foo/Bar/Baz[suffix]::Cat             v1.foo.bar        Baz.Cat
     *
     * @return string[]
     */
    public function services()
    {
        $services = [];
        foreach ($this->services as $item) {
            list($class, $namespace, $suffix) = $item;

            $namespace = substr($namespace, -1, 1) == '\\' ? $namespace : $namespace . '\\';
            $name      = str_replace($namespace, '', $class);

            $suffixLength = strlen($suffix);
            $name         = ($suffixLength > 0 and substr($name, -$suffixLength, $suffixLength) == $suffix) ? substr($name, 0, -$suffixLength) : $name;

            $slice   = array_filter(explode('\\', strtolower($name)));
            $version = '';
            if (isset($slice[0]) && stripos($slice[0], 'v') === 0) {
                $version = array_shift($slice) . '.';
            }
            switch (count($slice)) {
                case 0:
                    $name = '';
                    break;
                case 1:
                case 2:
                    $name = array_shift($slice);
                    break;
                default:
                    array_pop($slice);
                    $name = implode('.', $slice);
            }
            $services[] = $version . $name;
        }
        return $services;
    }

    /**
     * Register
     * 相同 Class 名，即便命名空间不同也会被覆盖
     * @param string $class
     * @param string $namespace
     * @param string $suffix
     * @throws \InvalidArgumentException
     */
    public function register(string $class, string $namespace = '', $suffix = '')
    {
        array_push($this->services, [$class, $namespace, $suffix]);

        $name         = basename(str_replace('\\', '/', $class));
        $suffixLength = strlen($suffix);
        $name         = ($suffixLength > 0 and substr($name, -$suffixLength, $suffixLength) == $suffix) ? substr($name, 0, -$suffixLength) : $name;

        if (!is_subclass_of($class, ServiceInterface::class)) {
            throw new \InvalidArgumentException(sprintf('%s is not a subclass of %s', $class, ServiceInterface::class));
        }

        $methods = get_class_methods($class);
        foreach ($methods as $method) {
            $this->callables[sprintf('/%s/%s', $class::NAME, $method)] = [$class, $method];
        }
    }

    /**
     * Start
     * @throws \Swoole\Exception
     */
    public function start()
    {
        $server = $this->server = new \Mix\Http\Server\Server($this->host, $this->port, $this->ssl, $this->reusePort);
        $server->set([
                'open_http2_protocol' => true,
            ] + $this->options);
        $server->start($this);
    }

    /**
     * 调用
     * @param ServerRequest $request
     * @param Response $response
     * @return Response
     * @throws \Exception
     * @throws NotFoundException
     */
    protected function call(ServerRequest $request, Response $response)
    {
        $path = $request->getUri()->getPath();
        if (!isset($this->callables[$path])) {
            throw new NotFoundException('Invalid uri');
        }

        list($class, $method) = $this->callables[$path];
        $reflectClass  = new ReflectionClass($class);
        $reflectMethod = $reflectClass->getMethod('call');

        if ($reflectMethod->getNumberOfParameters() >= 2) {
            $reflectParameter = $reflectMethod->getParameters()[1];
            $rpcRequestClass  = $reflectParameter->getClass()->getName();
            $rpcRequest       = new $rpcRequestClass;
            static::deserialize($rpcRequest, $request->getBody()->getContents());
        }

        // 执行
        $service    = new $class;
        $parameters = [];
        array_push($parameters, $request->getContext());
        isset($rpcRequest) and array_push($parameters, $rpcRequest);
        $rpcResponse = $this->process([$service, $method], $parameters);

        $content = static::serialize($rpcResponse);
        $body    = (new StreamFactory())->createStream($content);
        $response->withBody($body)
            ->withContentType('application/grpc')
            ->withHeader('trailer', 'grpc-status, grpc-message')
            ->withStatus(200);
        $swooleResponse = $response->getSwooleResponse();
        $swooleResponse->trailer('grpc-status', 0);
        $swooleResponse->trailer('grpc-message', '');

        return $response;
    }

    /**
     * Process
     * @param callable $callback
     * @param $parameters
     */
    protected function process(callable $callback, $parameters)
    {
        $microtime = static::microtime();
        $request   = $response = $error = null;
        try {
            $request  = $parameters[1] ?? null;
            $response = call_user_func_array($callback, $parameters);
        } catch (\Throwable $ex) {
            $message = sprintf('%s %s in %s on line %s', $ex->getMessage(), get_class($ex), $ex->getFile(), $ex->getLine());
            $code    = $ex->getCode();
            $error   = sprintf('[%d] %s', $code, $message);
        } finally {
            $this->dispatch($request, $response, $microtime, $error);
        }
    }

    /**
     * Handle HTTP
     * @param ServerRequest $request
     * @param \Mix\Http\Message\Response $response
     */
    public function handleHTTP(ServerRequest $request, \Mix\Http\Message\Response $response)
    {
        $method = $request->getMethod();
        if ($method != 'POST') {
            $this->show500(new \RuntimeException('Invalid method'), $response);
            return;
        }
        $contentType = $request->getHeaderLine('Content-Type');
        if (strpos($contentType, 'application/grpc') === false) {
            $this->show500(new \RuntimeException('Invalid content type'), $response);
            return;
        }

        $request->withContext(new Context());

        // 通过中间件执行
        $process    = function (ServerRequest $request, Response $response) use ($result) {
            try {
                $this->call($request, $response);
            } catch (NotFoundException $ex) {
                $this->show404($ex, $response);
            } catch (\Throwable $ex) {
                // 500 处理
                $this->show500($ex, $response);
                // 抛出错误，记录日志
                throw $ex;
            }
            return $response;
        };
        $dispatcher = new MiddlewareDispatcher($result->getMiddleware(), $process, $request, $response);
        $response   = $dispatcher->dispatch();

        /** @var Response $response */
        $response->end();
    }

    /**
     * 404 处理
     * @param \Throwable $exception
     * @param Response $response
     */
    public function show404(\Throwable $exception, Response $response)
    {
        return $response->withStatus(404)->end();
    }

    /**
     * 500 处理
     * @param \Throwable $exception
     * @param Response $response
     */
    public function show500(\Throwable $exception, Response $response)
    {
        return $response->withStatus(500)->end();
    }

    /**
     * Serialize
     * @param \Google\Protobuf\Internal\Message $message
     * @return string
     */
    static function serialize(\Google\Protobuf\Internal\Message $message): string
    {
        return static::pack($message->serializeToString());
    }

    /**
     * Deserialize
     * @param \Google\Protobuf\Internal\Message $message
     * @param string $data
     * @throws \Exception
     */
    static function deserialize(\Google\Protobuf\Internal\Message &$message, string $data)
    {
        $message->mergeFromString(static::unpack($data));
    }

    /**
     * Pack
     * @param string $data
     * @return string
     */
    static function pack(string $data): string
    {
        return $data = pack('CN', 0, strlen($data)) . $data;
    }

    /**
     * Unpack
     * @param string $data
     * @return string
     */
    static function unpack(string $data): string
    {
        // it's the way to verify the package length
        // 1 + 4 + data
        // $len = unpack('N', substr($data, 1, 4))[1];
        // assert(strlen($data) - 5 === $len);
        return $data = substr($data, 5);
    }

    /**
     * 获取当前时间, 单位: 秒, 粒度: 微秒
     * @return float
     */
    protected static function microtime()
    {
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }

    /**
     * Dispatch
     * @param $request
     * @param $response
     * @param float $microtime
     * @param null $error
     */
    protected function dispatch($request, $response, float $microtime, $error = null)
    {
        if (!isset($this->dispatcher)) {
            return;
        }
        $event           = new ProcessedEvent();
        $event->time     = round((static::microtime() - $microtime) * 1000, 2);
        $event->request  = $request;
        $event->response = $response;
        $event->error    = $error;
        $this->dispatcher->dispatch($event);
    }

    /**
     * Shutdown
     * @throws \Swoole\Exception
     */
    public function shutdown()
    {
        $this->server and $this->server->shutdown();
    }

}
