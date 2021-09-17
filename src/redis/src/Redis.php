<?php

namespace Mix\Redis;

use Mix\ObjectPool\Exception\WaitTimeoutException;
use Mix\Redis\Pool\ConnectionPool;
use Mix\Redis\Pool\Dialer;

/**
 * Class Redis
 * @package Mix\Redis
 */
class Redis implements ConnectionInterface
{

    use ScanTrait;

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
     * 最大活跃数
     * "0" 为不限制，"-1" 等于cpu数量
     * @var int
     */
    protected $maxOpen = -1;

    /**
     * 最多可空闲连接数
     * "-1" 等于cpu数量
     * @var int
     */
    protected $maxIdle = -1;

    /**
     * 连接可复用的最长时间
     * "0" 为不限制
     * @var int
     */
    protected $maxLifetime = 0;

    /**
     * 等待新连接超时时间
     * "0" 为不限制
     * @var float
     */
    protected $waitTimeout = 0.0;

    /**
     * 连接池
     * @var ConnectionPool
     */
    protected $pool;

    /**
     * @var Driver
     */
    protected $driver;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Redis constructor.
     * @param string $host
     * @param int $port
     * @param string $password
     * @param int $database
     * @param float $timeout
     * @param int $retryInterval
     * @param float|int $readTimeout
     * @throws \RedisException
     */
    public function __construct(string $host, int $port = 6379, string $password = '', int $database = 0, float $timeout = 5.0, int $retryInterval = 0, float $readTimeout = -1)
    {
        $this->host = $host;
        $this->port = $port;
        $this->password = $password;
        $this->database = $database;
        $this->timeout = $timeout;
        $this->retryInterval = $retryInterval;
        $this->readTimeout = $readTimeout;

        $this->driver = new Driver(
            $this->host,
            $this->port,
            $this->password,
            $this->database,
            $this->timeout,
            $this->retryInterval,
            $this->readTimeout
        );
    }

    protected function createPool()
    {
        if ($this->driver) {
            $this->driver->close();
            $this->driver = null;
        }

        $this->pool = new ConnectionPool(
            new Dialer(
                $this->host,
                $this->port,
                $this->password,
                $this->database,
                $this->timeout,
                $this->retryInterval,
                $this->readTimeout
            ),
            $this->maxOpen,
            $this->maxIdle,
            $this->maxLifetime,
            $this->waitTimeout
        );
    }

    /**
     * @param int $maxOpen
     * @param int $maxIdle
     * @param int $maxLifetime
     * @param float $waitTimeout
     */
    public function startPool(int $maxOpen, int $maxIdle, int $maxLifetime = 0, float $waitTimeout = 0.0)
    {
        $this->maxOpen = $maxOpen;
        $this->maxIdle = $maxIdle;
        $this->maxLifetime = $maxLifetime;
        $this->waitTimeout = $waitTimeout;
        $this->createPool();
    }

    /**
     * @param int $maxOpen
     */
    public function setMaxOpenConns(int $maxOpen)
    {
        if ($this->maxOpen == $maxOpen) {
            return;
        }
        $this->maxOpen = $maxOpen;
        $this->createPool();
    }

    /**
     * @param int $maxIdle
     */
    public function setMaxIdleConns(int $maxIdle)
    {
        if ($this->maxIdle == $maxIdle) {
            return;
        }
        $this->maxIdle = $maxIdle;
        $this->createPool();
    }

    /**
     * @param int $maxLifetime
     */
    public function setConnMaxLifetime(int $maxLifetime)
    {
        if ($this->maxLifetime == $maxLifetime) {
            return;
        }
        $this->maxLifetime = $maxLifetime;
        $this->createPool();
    }

    /**
     * @param float $waitTimeout
     */
    public function setPoolWaitTimeout(float $waitTimeout)
    {
        if ($this->waitTimeout == $waitTimeout) {
            return;
        }
        $this->waitTimeout = $waitTimeout;
        $this->createPool();
    }

    /**
     * @return array
     */
    public function poolStats(): array
    {
        if (!$this->pool) {
            return [];
        }
        return $this->pool->stats();
    }

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Borrow connection
     * @return Connection
     * @throws WaitTimeoutException
     */
    protected function borrow(): Connection
    {
        if ($this->pool) {
            $driver = $this->pool->borrow();
            $conn = new Connection($driver, $this->logger);
        } else {
            $conn = new Connection($this->driver, $this->logger);
        }
        return $conn;
    }

    /**
     * @param string ...$keys
     * @return Multi
     */
    public function watch(string ...$keys): Multi
    {
        return $this->borrow()->watch(...$keys);
    }

    /**
     * @return Multi
     */
    public function multi(): Multi
    {
        return $this->borrow()->multi();
    }

    /**
     * @return Pipeline
     */
    public function pipeline(): Pipeline
    {
        return $this->borrow()->pipeline();
    }

    /**
     * Call
     * @param $command
     * @param $arguments
     * @return mixed
     * @throws \RedisException
     */
    public function __call($command, $arguments)
    {
        return $this->borrow()->__call($command, $arguments);
    }

}
