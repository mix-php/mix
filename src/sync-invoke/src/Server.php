<?php

namespace Mix\SyncInvoke;

use Mix\Server\HandlerInterface;

/**
 * Class Server
 * @package Mix\SyncInvoke
 */
class Server
{

    /**
     * @var int
     */
    public $port = 0;

    /**
     * @var bool
     */
    public $reusePort = false;

    /**
     * @var HandlerInterface
     */
    public $handler;

    /**
     * @var \Mix\Server\Server
     */
    protected $server;

    /**
     * EOF
     */
    const EOF = "-Y3ac0v\n";

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
        $server = $this->server = new \Mix\Server\Server('127.0.0.1', $this->port, false, $this->reusePort);
        $server->set([
            'open_eof_check' => true,
            'package_eof'    => static::EOF,
        ]);
        if (!isset($this->handler)) {
            $this->handler = new Handler();
        }
        $server->start($this->handler);
    }

    /**
     * Shutdown
     * @throws \Swoole\Exception
     */
    public function shutdown()
    {
        $this->server->shutdown();
    }

}
