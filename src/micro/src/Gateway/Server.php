<?php

namespace Mix\Micro\Gateway;

use Mix\Http\Server\HandlerInterface;
use Mix\Http\Server\Server as HttpServer;

/**
 * Class Server
 * @package Mix\Micro\Gateway
 */
class Server
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
     * @var HandlerInterface
     */
    public $handler;

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
        $server->start($this->handler);
    }

    /**
     * Shutdown
     * @throws \Swoole\Exception
     */
    public function shutdown()
    {
        $this->handler->registry->clear();
        $this->httpServer->shutdown();
    }

}
