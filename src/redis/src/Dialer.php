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
     * 最大连接数
     * @var int
     */
    public $maxActive = 10;

    /**
     * 最多可空闲连接数
     * @var int
     */
    public $maxIdle = 5;

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
     * @param float $timeout
     * @param int $retryInterval
     * @param float $readTimeout
     * @return Redis
     * @throws \PhpDocReader\AnnotationException
     * @throws \ReflectionException
     */
    public function dial(string $host, int $port = 6379, string $password = '', int $database = 0, float $timeout = 5.0, int $retryInterval = 0, float $readTimeout = -1): Redis
    {
        $redis             = new Redis(
            $host,
            $port,
            $password,
            $database,
            $timeout,
            $retryInterval,
            $readTimeout
        );
        $redis->maxActive  = $this->maxActive;
        $redis->maxIdle    = $this->maxIdle;
        $redis->dispatcher = $this->dispatcher;
        return $redis;
    }

}
