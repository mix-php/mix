<?php

namespace Mix\Redis;

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
    protected $host = '';

    /**
     * 端口
     * @var int
     */
    protected $port = 6379;

    /**
     * 密码
     * @var string
     */
    protected $password = '';

    /**
     * 数据库
     * @var int
     */
    protected $database = 0;

    /**
     * 全局超时
     * @var float
     */
    protected $timeout = 5.0;

    /**
     * 重连间隔
     * @var int
     */
    protected $retryInterval = 0;

    /**
     * 读取超时
     * phpredis >= 3.1.3
     * @var float
     */
    protected $readTimeout = -1;

    /**
     * 最大连接数
     * @var int
     * @deprecated 废弃，使用 maxOpen 取代
     */
    public $maxActive = 8;

    /**
     * 最大活跃数
     * "0" 为不限制
     * @var int
     */
    public $maxOpen = 8;

    /**
     * 最多可空闲连接数
     * @var int
     */
    public $maxIdle = 8;

    /**
     * 连接可复用的最长时间
     * "0" 为不限制
     * @var int
     */
    public $maxLifetime = 0;

    /**
     * 等待新连接超时时间
     * "0" 为不限制
     * @var float
     */
    public $waitTimeout = 0.0;

    /**
     * 事件调度器
     * @var EventDispatcherInterface
     */
    public $dispatcher;

    /**
     * 连接池
     * @var ConnectionPool
     */
    protected $pool;

    /**
     * Redis constructor.
     * @param string $host
     * @param int $port
     * @param string $password
     * @param int $database
     * @param float $timeout
     * @param int $retryInterval
     * @param float $readTimeout
     * @throws \PhpDocReader\AnnotationException
     * @throws \ReflectionException
     */
    public function __construct(string $host, int $port = 6379, string $password = '', int $database = 0, float $timeout = 5.0, int $retryInterval = 0, float $readTimeout = -1)
    {
        $this->host          = $host;
        $this->port          = $port;
        $this->password      = $password;
        $this->database      = $database;
        $this->timeout       = $timeout;
        $this->retryInterval = $retryInterval;
        $this->readTimeout   = $readTimeout;

        $this->maxOpen = &$this->maxActive; // 兼容旧版

        $pool              = new ConnectionPool(
            new Dialer([
                'host'          => $this->host,
                'port'          => $this->port,
                'password'      => $this->password,
                'database'      => $this->database,
                'timeout'       => $this->timeout,
                'retryInterval' => $this->retryInterval,
                'readTimeout'   => $this->readTimeout,
            ])
        );
        $pool->maxOpen     = &$this->maxOpen;
        $pool->maxIdle     = &$this->maxIdle;
        $pool->maxLifetime = &$this->maxLifetime;
        $pool->waitTimeout = &$this->waitTimeout;
        $pool->dispatcher  = &$this->dispatcher;
        $this->pool        = $pool;
    }

    /**
     * Borrow connection
     * @return Connection
     */
    public function borrow(): Connection
    {
        $driver           = $this->pool->borrow();
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
        $conn = $this->borrow();
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
        return $this->borrow()->__call($name, $arguments);
    }

}
