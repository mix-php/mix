<?php

namespace Mix\Micro\Gateway;

use Mix\Http\Message\Factory\StreamFactory;
use Mix\Http\Message\Response;
use Mix\Http\Message\ServerRequest;
use Mix\Http\Server\Server as HttpServer;
use Mix\Http\Server\HandlerInterface;
use Mix\Micro\Exception\Gateway\ProxyException;
use Mix\Micro\Exception\NotFoundException;
use Mix\Micro\Gateway\Event\AccessEvent;
use Mix\Micro\RegistryInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

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

        if (isset($map[$path])) {
            /** @var ProxyInterface $proxy */
            $proxy = array_pop($map[$path]);
            try {
                $serivce = $proxy->service($this->registry, $request);
                $status  = $proxy->proxy($serivce, $request, $response);
                $this->dispatch($microtime, $status, $request, $response, $serivce);
            } catch (NotFoundException $ex) {
                $proxy->show404($ex, $response);
                $this->dispatch($microtime, 404, $request, $response, $serivce ?? null, sprintf('[%d] %s', $ex->getCode(), $ex->getMessage()));
            } catch (ProxyException $ex) {
                $proxy->show500($ex, $response);
                $this->dispatch($microtime, 500, $request, $response, $serivce ?? null, sprintf('[%d] %s', $ex->getCode(), $ex->getMessage()));
            }
            return;
        }

        foreach ($map['/'] ?? [] as $proxy) {
            try {
                $serivce = $proxy->service($this->registry, $request);
                $status  = $proxy->proxy($serivce, $request, $response);
                $this->dispatch($microtime, $status, $request, $response, $serivce);
                return;
            } catch (NotFoundException $ex) {
            } catch (ProxyException $ex) {
                $proxy->show500($ex, $response);
                $this->dispatch($microtime, 500, $request, $response, $serivce ?? null, sprintf('[%d] %s', $ex->getCode(), $ex->getMessage()));
            }
        }
        $ex = new \Exception(sprintf('Uri %s not found', $request->getUri()->__toString()));
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
     * 获取微秒时间
     * @return float
     */
    protected static function microtime()
    {
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }

    /**
     * 404 处理
     * @param \Exception $exception
     * @param Response $response
     * @return void
     */
    public function show404(\Exception $exception, Response $response)
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
        $this->httpServer->shutdown();
    }

}
