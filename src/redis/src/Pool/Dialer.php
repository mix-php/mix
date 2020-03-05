<?php

namespace Mix\Redis\Pool;

use Mix\Bean\BeanInjector;
use Mix\Pool\DialerInterface;
use Mix\Redis\Connection;
use Psr\EventDispatcher\EventDispatcherInterface;

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
     * 数据库
     * @var int
     */
    public $database = 0;

    /**
     * 密码
     * @var string
     */
    public $password = '';

    /**
     * 事件调度器
     * @var EventDispatcherInterface
     */
    public $eventDispatcher;

    /**
     * Dialer constructor.
     * @param array $config
     * @throws \PhpDocReader\AnnotationException
     * @throws \ReflectionException
     */
    public function __construct(array $config = [])
    {
        BeanInjector::inject($this, $config);
    }

    /**
     * Dial
     * @return Connection
     */
    public function dial()
    {
        $conn = new Connection([
            'host'            => $this->host,
            'port'            => $this->port,
            'timeout'         => $this->timeout,
            'retryInterval'   => $this->retryInterval,
            'readTimeout'     => $this->readTimeout,
            'database'        => $this->database,
            'password'        => $this->password,
            'eventDispatcher' => $this->eventDispatcher,
        ]);
        $conn->connect();
        return $conn;
    }

}
