<?php

namespace Mix\Micro\Gateway;

use Mix\Http\Message\Factory\StreamFactory;
use Mix\Http\Message\Response;
use Mix\Http\Message\ServerRequest;
use Mix\Http\Server\Middleware\MiddlewareDispatcher;
use Mix\Http\Server\Server as HttpServer;
use Mix\Http\Server\HandlerInterface;
use Mix\Micro\Gateway\Exception\ProxyException;
use Mix\Micro\Register\Exception\NotFoundException;
use Mix\Micro\Gateway\Event\AccessEvent;
use Mix\Micro\Register\RegistryInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Server\MiddlewareInterface;

/**
 * Class Server
 * @package Mix\Micro\Gateway
 */
class Server implements HandlerInterface
{

    /**
     * @var int
     */
    public $port = 9595;

    /**
     * @var bool
     */
    public $reusePort = false;

    /**
     * @var ProxyInterface[]
     */
    public $proxies = [];

    /**
     * @var string[] MiddlewareInterface class
     */
    public $middleware = [];

    /**
     * @var RegistryInterface
     */
    public $registry;

    /**
     * @var EventDispatcherInterface
     */
    public $dispatcher;

    /**
     * @var string
     */
    protected $host = '0.0.0.0';

    /**
     * @var bool
     */
    protected $ssl = false;

    /**
     * @var HttpServer
     */
    protected $httpServer;

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var ProxyInterface[][]
     */
    protected $proxyMap = [];

    /**
     * Server constructor.
     * @param int $port
     * @param bool $reusePort
     */
    public function __construct(int $port, bool $reusePort = false)
    {
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
     * Start
     * @throws \Swoole\Exception
     */
    public function start()
    {
        // 解析
        foreach ($this->proxies as $proxy) {
            $this->proxyMap[$proxy->pattern()][] = $proxy;
        }
        // 启动
        $server = $this->httpServer = new HttpServer($this->host, $this->port, $this->ssl, $this->reusePort);
        $server->set($this->options);
        $server->start($this);
    }

    /**
     * Handle HTTP
     * @param ServerRequest $request
     * @param Response $response
     */
    public function handleHTTP(ServerRequest $request, Response $response)
    {
        $microtime = static::microtime();
        $map       = $this->proxyMap;
        $path      = $request->getUri()->getPath();

        $dispatcher = new MiddlewareDispatcher($this->middleware, $request, $response);
        $response   = $dispatcher->dispatch();
        if (!is_null($response->getBody())) {
            $response->end();
            return;
        }

        $proxys = $map[$path] ?? ($map['/'] ?? []);
        /** @var ProxyInterface $proxy */
        $proxy = array_pop($proxys);
        if ($proxy) {
            try {
                $serivce = $proxy->service($this->registry, $request);
                $status  = $proxy->proxy($serivce, $request, $response);
                $this->dispatch($microtime, $status, $request, $response, $serivce);
            } catch (NotFoundException $ex) {
                $proxy->show404($ex, $response);
                $this->dispatch($microtime, 404, $request, $response, null, sprintf('[%d] %s', $ex->getCode(), $ex->getMessage()));
            } catch (\Exception $ex) {
                $proxy->show500($ex, $response);
                $this->dispatch($microtime, 500, $request, $response, $serivce ?? null, sprintf('[%d] %s', $ex->getCode(), $ex->getMessage()));
            }
            return;
        }

        $ex = new NotFoundException(sprintf('Uri %s not found', $request->getUri()->__toString()));
        $this->show404($ex, $response);
        $this->dispatch($microtime, 404, $request, $response, null, sprintf('[%d] %s', $ex->getCode(), $ex->getMessage()));
    }

    /**
     * Dispatch
     * @param $microtime
     * @param $status
     * @param $request
     * @param $response
     * @param $service
     * @param $error
     */
    protected function dispatch($microtime, $status, $request, $response, $service = null, $error = null)
    {
        if (!isset($this->dispatcher)) {
            return;
        }
        $event           = new AccessEvent();
        $event->time     = round((static::microtime() - $microtime) * 1000, 2);
        $event->status   = $status;
        $event->request  = $request;
        $event->response = $response;
        $event->service  = $service;
        $event->error    = $error;
        $this->dispatcher->dispatch($event);
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
     * 404 处理
     * @param \Throwable $exception
     * @param Response $response
     * @return void
     */
    public function show404(\Throwable $exception, Response $response)
    {
        $content = '404 Not Found';
        $body    = (new StreamFactory())->createStream($content);
        $response
            ->withContentType('text/plain')
            ->withBody($body)
            ->withStatus(404)
            ->end();
    }

    /**
     * Shutdown
     * @throws \Swoole\Exception
     */
    public function shutdown()
    {
        foreach ($this->proxyMap as $values) {
            foreach ($values as $proxy) {
                $proxy->close();
            }
        }
        $this->registry and $this->registry->close();
        $this->httpServer and $this->httpServer->shutdown();
    }

}
