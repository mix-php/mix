<?php

namespace Mix\SyncInvoke\Client;

use Mix\ObjectPool\ObjectTrait;
use Mix\SyncInvoke\Constants;
use Swoole\Coroutine\Client;

/**
 * Class Driver
 * @package Mix\SyncInvoke\Client
 */
class Driver
{
    
    use ObjectTrait;

    /**
     * @var int
     */
    public $port = 0;

    /**
     * Global timeout
     * @var float
     */
    public $timeout = 0.0;

    /**
     * @var Client
     */
    protected $client;

    /**
     * Driver constructor.
     * @param int $port
     * @param float $timeout
     * @throws \Swoole\Exception
     */
    public function __construct(int $port, float $timeout)
    {
        $this->port    = $port;
        $this->timeout = $timeout;
        $this->connect();
    }

    /**
     * Get instance
     * @return Client
     */
    public function instance()
    {
        return $this->client;
    }

    /**
     * Connect
     * @throws \Swoole\Exception
     */
    public function connect()
    {
        $port    = $this->port;
        $timeout = $this->timeout;
        $client  = new Client(SWOOLE_SOCK_TCP);
        $client->set([
            'open_eof_check' => true,
            'package_eof'    => Constants::EOF,
        ]);
        if (!$client->connect('127.0.0.1', $port, $timeout)) {
            throw new \Swoole\Exception(sprintf("Sync invoke: %s (port: %s)", $client->errMsg, $port), $client->errCode);
        }
        $this->client = $client;
    }

    /**
     * Close
     * @throws \Swoole\Exception
     */
    public function close()
    {
        if ($this->client->close()) {
            return;
        }
        $errMsg  = $this->client->errMsg;
        $errCode = $this->client->errCode;
        if ($errMsg == '' && $errCode == 0) {
            return;
        }
        throw new \Swoole\Exception($errMsg, $errCode);
    }

}
