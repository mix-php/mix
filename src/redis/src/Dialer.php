<?php

namespace Mix\Redis;

use Mix\Bean\BeanInjector;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * Class Dialer
 * @package Mix\Database
 */
class Dialer
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
     * 最多可空闲连接数
     * @var int
     */
    public $maxIdle = 5;

    /**
     * 最大连接数
     * @var int
     */
    public $maxActive = 5;

    /**
     * 事件调度器
     * @var EventDispatcherInterface
     */
    public $dispatcher;

    /**
     * AbstractConnection constructor.
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        BeanInjector::inject($this, $config);
    }

    /**
     * Dial
     * @param string $host
     * @param int $port
     * @param string $password
     * @param int $database
     * @return Redis
     */
    public function dial(string $host, int $port, string $password, int $database = 0): Redis
    {
        $redis = new Redis([
            'host'          => $host,
            'port'          => $port,
            'password'      => $password,
            'database'      => $database,
            'timeout'       => $this->timeout,
            'retryInterval' => $this->retryInterval,
            'readTimeout'   => $this->readTimeout,
            'maxIdle'       => $this->maxIdle,
            'maxActive'     => $this->maxActive,
            'dispatcher'    => $this->dispatcher,
        ]);
        $redis->init();
        return $redis;
    }

}
