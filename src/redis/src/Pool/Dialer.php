<?php

namespace Mix\Redis\Pool;

use Mix\ObjectPool\DialerInterface;
use Mix\Redis\Driver;

/**
 * Class Dialer
 * @package Mix\Redis\Pool
 */
class Dialer implements DialerInterface
{

    /**
     * 主机
     * @var string
     */
    public $host = '';

    /**
     * 端口
     * @var int
     */
    public $port = 6379;

    /**
     * 密码
     * @var string
     */
    public $password = '';

    /**
     * 数据库
     * @var int
     */
    public $database = 0;

    /**
     * 超时
     * @var float
     */
    public $timeout = 5.0;

    /**
     * 重连间隔
     * @var int
     */
    public $retryInterval = 0;

    /**
     * 读取超时
     * phpredis >= 3.1.3
     * @var int
     */
    public $readTimeout = -1;

    /**
     * Dialer constructor.
     * @param string $host
     * @param int $port
     * @param string $password
     * @param float $timeout
     * @param int $retryInterval
     * @param int $readTimeout
     */
    public function __construct(string $host, int $port, string $password, int $database = 0, float $timeout = 5.0, int $retryInterval = 0, int $readTimeout = -1)
    {
        $this->host = $host;
        $this->port = $port;
        $this->password = $password;
        $this->database = $database;
        $this->timeout = $timeout;
        $this->retryInterval = $retryInterval;
        $this->readTimeout = $readTimeout;
    }

    /**
     * @return Driver
     */
    public function dial(): object
    {
        return new Driver(
            $this->host,
            $this->port,
            $this->password,
            $this->database,
            $this->timeout,
            $this->retryInterval,
            $this->readTimeout
        );
    }

}
