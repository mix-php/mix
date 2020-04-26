<?php

namespace Mix\Redis;

use Mix\Bean\BeanInjector;
use Mix\Redis\Pool\ConnectionPool;
use Mix\Redis\Pool\Dialer;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * Class Redis
 * @package Mix\Redis
 */
class Redis implements ConnectionInterface
{

    use ReferenceTrait;

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
     * @var ConnectionPool
     */
    protected $pool;

    /**
     * AbstractConnection constructor.
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        BeanInjector::inject($this, $config);
    }

    /**
     * Init
     */
    public function init()
    {
        $pool       = new ConnectionPool([
            'maxIdle'    => $this->maxIdle,
            'maxActive'  => $this->maxActive,
            'dialer'     => new Dialer([
                'host'          => $this->host,
                'port'          => $this->port,
                'password'      => $this->password,
                'database'      => $this->database,
                'timeout'       => $this->timeout,
                'retryInterval' => $this->retryInterval,
                'readTimeout'   => $this->readTimeout,
            ]),
            'dispatcher' => $this->dispatcher,
        ]);
        $this->pool = $pool;
    }

    /**
     * Open connection
     * @return Connection
     */
    public function open(): Connection
    {
        $driver           = $this->pool->get();
        $conn             = new Connection($driver);
        $conn->dispatcher = $this->dispatcher;
        return $conn;
    }

    /**
     * Multi
     * @param int $mode
     * @return Connection
     */
    public function multi($mode = \Redis::MULTI): Connection
    {
        $conn = $this->open();
        $conn->__call(__FUNCTION__, [$mode]);
        return $conn;
    }

    /**
     * Disable exec
     * @return array
     * @deprecated 不可直接使用，请在 multi 返回的连接中使用
     */
    public function exec()
    {
        throw new \RedisException('Exec unavailable, please use in the connection returned by multi');
    }

    /**
     * Call
     * @param $name
     * @param $arguments
     * @return mixed
     * @throws \RedisException
     * @throws \Throwable
     */
    public function __call($name, $arguments)
    {
        return $this->open()->__call($name, $arguments);
    }

}
