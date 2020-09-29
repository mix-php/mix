<?php

namespace Mix\Grpc;

use Mix\Context\Context;
use Mix\Grpc\Event\ProcessedEvent;
use Mix\Grpc\Exception\NotFoundException;
use Mix\Grpc\Helper\GrpcHelper;
use Mix\Grpc\Middleware\ProxyMiddleware;
use Mix\Http\Message\Factory\StreamFactory;
use Mix\Http\Message\Request;
use Mix\Http\Message\Response;
use Mix\Http\Message\ServerRequest;
use Mix\Http\Message\Stream\ContentStream;
use Mix\Http\Server\Middleware\MiddlewareDispatcher;
use Mix\Http\Server\ServerHandlerInterface;
use Mix\Micro\Server\ServerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * Class Server
 * @package Mix\Grpc
 */
class Server implements ServerHandlerInterface, ServerInterface
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
     * @var string[]
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
     * @param bool $ssl
     * @param bool $reusePort
     */
    public function __construct(string $host = '0.0.0.0', int $port = 0, bool $ssl = false, bool $reusePort = false)
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
     * Host
     * @return string
     */
    public function host()
    {
        return $this->host;
    }

    /**
     * Port
     * @return int
     */
    public function port()
    {
        return $this->port;
    }

    /**
     * 获取全部 service 名称
     * @return string[][] [name => [class,...]]
     */
    public function services()
    {
        return $this->services;
    }

    /**
     * Register
     * @param string $class
     * @throws \InvalidArgumentException
     */
    public function register(string $class)
    {
        if (!is_subclass_of($class, ServiceInterface::class)) {
            throw new \InvalidArgumentException(sprintf('%s is not a subclass of %s', $class, ServiceInterface::class));
        }

        $name = $class::NAME;
        if (!$name) {
            throw new \InvalidArgumentException(sprintf('Const %s::NAME can\'t be empty', $class));
        }

        $slice                      = explode('.', $name);
        $className                  = array_pop($slice);
        $service                    = implode('.', $slice);
        $this->services[$service][] = $class;

        $methods      = get_class_methods($class);
        $reflectClass = new \ReflectionClass($class);
        foreach ($methods as $method) {
            if (strpos($method, '_') === 0) {
                continue;
            }

            $reflectMethod = $reflectClass->getMethod($method);
            if ($reflectMethod->getNumberOfParameters() != 2) {
                throw new \InvalidArgumentException(sprintf('%s::%s wrong number of parameters', $class, $method));
            }

            $this->callables[sprintf('/%s/%s', $name, $method)] = [$class, $method, $service, sprintf('%s.%s', $className, $method)];
        }
    }

    /**
     * Start
     * @throws \Swoole\Exception
     */
    public function start()
    {
        $server       = $this->server = new \Mix\Http\Server\Server($this->host, $this->port, $this->ssl, $this->reusePort);
        $server->port = &$this->port; // 当随机分配端口时同步端口信息
        $server->set(['open_http2_protocol' => true] + $this->options + ['http_compression' => false]);
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
        $response->withContentType('application/grpc');

        list($class, $method, $service, $endpoint) = $this->callables[$path];

        $reflectClass     = new \ReflectionClass($class);
        $reflectMethod    = $reflectClass->getMethod($method);
        $reflectParameter = $reflectMethod->getParameters()[1];
        $rpcRequestClass  = $reflectParameter->getClass()->getName();
        $rpcRequest       = new $rpcRequestClass;
        GrpcHelper::deserialize($rpcRequest, $request->getBody()->getContents());

        // 执行
        $object     = new $class;
        $parameters = [];
        array_push($parameters, $request->getContext());
        array_push($parameters, $rpcRequest);
        $rpcResponse = $this->process([$object, $method], $parameters, $service, $endpoint);

        $content = GrpcHelper::serialize($rpcResponse);
        $body    = (new StreamFactory())->createStream($content);
        $response->withBody($body)->withStatus(200);

        return $response;
    }

    /**
     * Process
     * @param callable $callback
     * @param array $parameters
     * @param string $service
     * @param string $endpoint
     * @throws \Throwable
     */
    protected function process(callable $callback, array $parameters, string $service, string $endpoint)
    {
        $microtime = GrpcHelper::microtime();
        $request   = $response = $error = null;
        try {
            $request  = $parameters[1] ?? null;
            $response = call_user_func_array($callback, $parameters);
            return $response;
        } catch (\Throwable $ex) {
            $message = sprintf('%s %s in %s on line %s', $ex->getMessage(), get_class($ex), $ex->getFile(), $ex->getLine());
            $code    = $ex->getCode();
            $error   = sprintf('[%d] %s', $code, $message);
            throw $ex;
        } finally {
            $this->dispatch($request, $response, $service, $endpoint, $microtime, $error);
        }
    }

    /**
     * Handle HTTP
     * @param ServerRequest $request
     * @param \Mix\Http\Message\Response $response
     */
    public function handleHTTP(ServerRequest $request, \Mix\Http\Message\Response $response)
    {
        $method      = $request->getMethod();
        $contentType = $request->getHeaderLine('Content-Type');
        $isGrpc      = in_array($contentType, [
            'application/grpc', // micro v1 and raw grpc
            'application/json', // micro v1 proxy
            'application/grpc+proto', // micro v2
            'application/grpc+json', // micro v2 proxy
        ]);
        if (!$isGrpc || $method != 'POST') {
            $this->error500(new \RuntimeException('Invalid request'), $response)->send();
            return;
        }

        $trailerFunc = function (ServerRequest $request, \Mix\Http\Message\Response $response, \Throwable $ex = null) {
            if (!in_array($request->getHeaderLine('Content-Type'), [
                'application/grpc',
                'application/grpc+proto',
            ])) {
                return;
            }
            $response->withHeader('trailer', 'grpc-status, grpc-message');
            $swooleResponse = $response->getSwooleResponse();
            if (!is_null($ex)) {
                $swooleResponse->trailer('grpc-status', $ex->getCode() == 0 ? -1 : $ex->getCode());
                $swooleResponse->trailer('grpc-message', $ex->getMessage());
            } else {
                $swooleResponse->trailer('grpc-status', 0);
                $swooleResponse->trailer('grpc-message', '');
            }
        };

        $request->withContext(new Context());

        // 通过中间件执行
        try {
            $process    = function (ServerRequest $request, Response $response) {
                return $this->call($request, $response);
            };
            $middleware = $this->middleware;
            array_unshift($middleware, ProxyMiddleware::class);
            $dispatcher = new MiddlewareDispatcher($middleware, $process, $request, $response);
            $response   = $dispatcher->dispatch();
            $trailerFunc($request, $response);
        } catch (NotFoundException $ex) {
            $trailerFunc($request, $response, $ex);
            $this->error404($ex, $response)->send();
            return;
        } catch (\Throwable $ex) {
            // grpc错误传递处理
            $trailerFunc($request, $response, $ex);
            $response->withStatus(200)->send();
            // 抛出错误，记录日志
            throw $ex;
        }

        /** @var Response $response */
        $response->send();
    }

    /**
     * 404 处理
     * @param \Throwable $exception
     * @param Response $response
     * @return Response
     */
    public function error404(\Throwable $exception, Response $response): Response
    {
        return $response->withStatus(404);
    }

    /**
     * 500 处理
     * @param \Throwable $exception
     * @param Response $response
     * @return Response
     */
    public function error500(\Throwable $exception, Response $response): Response
    {
        return $response->withStatus(500);
    }

    /**
     * Dispatch
     * @param $request
     * @param $response
     * @param string $service
     * @param string $method
     * @param float $microtime
     * @param string|null $error
     */
    protected function dispatch($request, $response, string $service, string $method, float $microtime, string $error = null)
    {
        if (!isset($this->dispatcher)) {
            return;
        }
        $event           = new ProcessedEvent();
        $event->time     = round((GrpcHelper::microtime() - $microtime) * 1000, 2);
        $event->request  = $request;
        $event->response = $response;
        $event->service  = $service;
        $event->method   = $method;
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
