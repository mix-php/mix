<?php

namespace Mix\Micro\Gateway;

use Mix\Http\Message\Response;
use Mix\Http\Message\ServerRequest;
use Mix\Http\Server\Server as HttpServer;
use Mix\Http\Server\HandlerInterface;
use Mix\Micro\Gateway\Proxy\WebOrApiProxy;

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
     * @var WebOrApiProxy
     */
    public $webOrApiProxy;

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
        $server = $this->httpServer = new HttpServer($this->host, $this->port, $this->ssl, $this->reusePort);
        $server->start($this);
    }

    /**
     * Handle http
     * @param ServerRequest $request
     * @param Response $response
     */
    public function handleHTTP(ServerRequest $request, Response $response)
    {
        $path = $request->getUri()->getPath();
        switch ($path) {
            case '/jsonrpc':
                break;
            default:
                $this->webOrApiProxy->proxy($this, $request, $response);
        }
    }

    /**
     * Shutdown
     * @throws \Swoole\Exception
     */
    public function shutdown()
    {
        $this->webOrApiProxy->close();
        $this->httpServer->shutdown();
    }

}
