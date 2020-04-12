<?php

namespace Mix\JsonRpc;

use Mix\Concurrent\Sync\WaitGroup;
use Mix\Http\Message\Factory\StreamFactory;
use Mix\Http\Message\ServerRequest;
use Mix\JsonRpc\Event\ProcessedEvent;
use Mix\JsonRpc\Factory\ResponseFactory;
use Mix\JsonRpc\Helper\JsonRpcHelper;
use Mix\JsonRpc\Message\Request;
use Mix\JsonRpc\Message\Response;
use Mix\JsonRpc\Middleware\MiddlewareDispatcher;
use Mix\JsonRpc\Middleware\MiddlewareInterface;
use Mix\Server\Connection;
use Mix\Server\Exception\ReceiveException;
use Psr\EventDispatcher\EventDispatcherInterface;
use Swoole\Coroutine\Channel;

/**
 * Class Server
 * @package Mix\JsonRpc
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
     * @var \Mix\Server\Server
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
    public function __construct(string $host, int $port, bool $reusePort = false)
    {
        $this->host      = $host;
        $this->port      = $port;
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
     */
    public function register(string $class, string $namespace = '', $suffix = '')
    {
        array_push($this->services, [$class, $namespace, $suffix]);

        $name         = basename(str_replace('\\', '/', $class));
        $suffixLength = strlen($suffix);
        $name         = ($suffixLength > 0 and substr($name, -$suffixLength, $suffixLength) == $suffix) ? substr($name, 0, -$suffixLength) : $name;

        $methods = get_class_methods($class);
        foreach ($methods as $method) {
            $this->callables[sprintf('%s.%s', $name, $method)] = [$class, $method];
        }
    }

    /**
     * Start
     * @throws \Swoole\Exception
     */
    public function start()
    {
        $server = $this->server = new \Mix\Server\Server($this->host, $this->port, false, $this->reusePort);
        $server->set([
                'open_eof_check' => true,
                'package_eof'    => Constants::EOF,
            ] + $this->options);
        $server->start($this);
    }

    /**
     * 连接处理
     * @param Connection $conn
     * @throws \Throwable
     */
    public function handle(Connection $conn)
    {
        // 消息发送
        $sendChan = new Channel();
        xdefer(function () use ($sendChan) {
            $sendChan->close();
        });
        xgo(function () use ($sendChan, $conn) {
            while (true) {
                $data = $sendChan->pop();
                if (!$data) {
                    return;
                }
                try {
                    $conn->send($data);
                } catch (\Throwable $e) {
                    $conn->close();
                    throw $e;
                }
            }
        });
        // 消息读取
        while (true) {
            try {
                $data = $conn->recv();
                $this->callTCP($sendChan, $data);
            } catch (\Throwable $e) {
                // 忽略服务器主动断开连接异常
                if ($e instanceof ReceiveException) {
                    return;
                }
                // 抛出异常
                throw $e;
            }
        }
    }

    /**
     * 调用TCP
     * @param Channel $sendChan
     * @param string $payload
     */
    protected function callTCP(Channel $sendChan, string $payload)
    {
        // 反序列化
        try {
            $request = JsonRpcHelper::deserializeRequestFromTCP($payload);
        } catch (\Throwable $ex) {
            $response = (new ResponseFactory)->createErrorResponse(-32700, 'Parse error', null);
            $sendChan->push(JsonRpcHelper::serializeResponse($response));
            return;
        }

        // 通过中间件执行
        $process             = function (Request $request) {
            return $this->process($request);
        };
        $interceptDispatcher = new MiddlewareDispatcher($this->middleware, $process, $request);
        $response            = $interceptDispatcher->dispatch();

        // 发送
        $sendChan->push(JsonRpcHelper::serializeResponse($response));
    }

    /**
     * 调用HTTP
     * @param ServerRequest $request
     * @param \Mix\Http\Message\Response $httpResponse
     * @param string $content
     */
    protected function callHTTP(ServerRequest $request, \Mix\Http\Message\Response $httpResponse)
    {
        // 反序列化
        try {
            $contents = $request->getBody()->getContents();
            $request  = JsonRpcHelper::deserializeRequestFromHTTP($contents);
        } catch (\Throwable $ex) {
            $response = (new ResponseFactory)->createErrorResponse(-32700, 'Parse error', null);
            $body     = (new StreamFactory)->createStream(JsonRpcHelper::serializeResponse(true, $response));
            $httpResponse->withBody($body)
                ->withContentType('application/json')
                ->withStatus(200)
                ->end();
            return;
        }

        // 通过中间件执行
        $process             = function (Request $request) {
            return $this->process($request);
        };
        $interceptDispatcher = new MiddlewareDispatcher($this->middleware, $process, $request);
        $response            = $interceptDispatcher->dispatch();

        // 发送
        $body = (new StreamFactory)->createStream(JsonRpcHelper::serializeResponse($response));
        $httpResponse->withBody($body)
            ->withContentType('application/json')
            ->withStatus(200)
            ->end();
    }

    /**
     * 处理
     * @param Request $request
     * @return Response
     */
    protected function process(Request $request)
    {
        // 执行
        $microtime = JsonRpcHelper::microtime();
        try {
            // 验证
            if (!JsonRpcHelper::validRequest($request)) {
                throw new \RuntimeException('Invalid Request', -32600);
            }
            if (!isset($this->callables[$request->method])) {
                throw new \RuntimeException(sprintf('Method %s not found', $request->method), -32601);
            }
            // 执行
            list($class, $method) = $this->callables[$request->method];
            $callable = [new $class($request), $method];
            $params   = $request->params;
            if (!is_array($params)) {
                throw new \RuntimeException('Params only array type can be used');
            }
            array_unshift($params, $request->context);
            $result   = call_user_func($callable, ...$params);
            $response = (new ResponseFactory)->createResultResponse($result, $request->id);
        } catch (\Throwable $ex) {
            $message  = sprintf('%s %s in %s on line %s', $ex->getMessage(), get_class($ex), $ex->getFile(), $ex->getLine());
            $code     = $ex->getCode();
            $response = (new ResponseFactory)->createErrorResponse($code, $ex->getMessage(), $request->id);
            $error    = sprintf('[%d] %s', $code, $message);
        } finally {
            $this->dispatch($request, $response, $microtime, $error ?? null);
        }
        return $response;
    }

    /**
     * Handle HTTP
     * @param ServerRequest $request
     * @param \Mix\Http\Message\Response $response
     */
    public function handleHTTP(ServerRequest $request, \Mix\Http\Message\Response $response)
    {
        $contentType = $request->getHeaderLine('Content-Type');
        $method      = $request->getMethod();
        if (strpos($contentType, 'application/json') === false || $method != 'POST') {
            $response->withStatus(500)->end();
            return;
        }
        $this->callHTTP($request, $response);
    }

    /**
     * Dispatch
     * @param Request $request
     * @param Response $response
     * @param float $microtime
     * @param null $error
     */
    protected function dispatch(Request $request, Response $response, float $microtime, $error = null)
    {
        if (!isset($this->dispatcher)) {
            return;
        }
        $event           = new ProcessedEvent();
        $event->time     = round((JsonRpcHelper::microtime() - $microtime) * 1000, 2);
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
