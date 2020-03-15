<?php

namespace Mix\Micro\Gateway;

use Mix\Http\Message\Factory\StreamFactory;
use Mix\Http\Server\Server as HttpServer;
use Mix\Http\Server\HandlerInterface;
use Mix\Micro\Exception\NotFoundException;
use Mix\Micro\Gateway\Event\AccessEvent;
use Mix\Micro\RegistryInterface;
use Mix\Micro\ServiceInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

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
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     */
    public function handleHTTP(ServerRequestInterface $request, ResponseInterface $response)
    {
        $map     = $this->proxyMap;
        $path    = $request->getUri()->getPath();
        $pattern = isset($map[$path]) ? $path : '/';
        foreach ($map[$pattern] ?? [] as $proxy) {
            try {
                $serivce = $this->service($path, $proxy->namespace());
                $status  = $proxy->proxy($serivce, $request, $response);
                if ($status == 502) {
                    static::show502($response);
                }
                $event           = new AccessEvent();
                $event->status   = $status;
                $event->request  = $request;
                $event->response = $response;
                $event->service  = $serivce;
                $this->dispatch($event);
                return;
            } catch (NotFoundException $ex) {
            }
        }
        static::show404($response);
        $event           = new AccessEvent();
        $event->status   = 404;
        $event->request  = $request;
        $event->response = $response;
        $this->dispatch($event);
    }

    /**
     * Get service
     *
     * Url                  Service        Method
     * /foo/bar             foo            Foo.Bar
     * /foo/bar/baz         foo            Bar.Baz
     * /foo/bar/baz/cat     foo.bar        Baz.Cat
     *
     * @param string $path
     * @param string $namespace
     * @return ServiceInterface
     */
    protected function service(string $path, string $namespace)
    {
        $slice = array_filter(explode('/', $path));
        switch (count($slice)) {
            case 0:
            case 1:
                throw new NotFoundException('Invalid proxy path');
                break;
            case 2:
            case 3:
                $name = array_shift($slice);
                break;
            default:
                array_pop($slice);
                array_pop($slice);
                $name = implode('/', $slice);
        }
        return $this->registry->get(sprintf('%s.%s', $namespace, $name));
    }

    /**
     * Dispatch
     * @param object $event
     */
    protected function dispatch(object $event)
    {
        if (!isset($this->dispatcher)) {
            return;
        }
        $this->dispatcher->dispatch($event);
    }

    /**
     * 404 处理
     * @param Response $response
     * @return void
     */
    public static function show404(Response $response)
    {
        $content = '404 Not Found';
        $body    = (new StreamFactory())->createStream($content);
        return $response
            ->withContentType('text/plain')
            ->withBody($body)
            ->withStatus(404)
            ->end();
    }

    /**
     * 502 处理,
     * @param Response $response
     * @return void
     */
    public static function show502(Response $response)
    {
        $content = '502 Bad Gateway';
        $body    = (new StreamFactory())->createStream($content);
        return $response
            ->withContentType('text/plain')
            ->withBody($body)
            ->withStatus(502)
            ->end();
    }

    /**
     * Shutdown
     * @throws \Swoole\Exception
     */
    public function shutdown()
    {
        foreach ($this->proxies as $proxy) {
            $proxy->close();
        }
        $this->httpServer->shutdown();
    }

}
