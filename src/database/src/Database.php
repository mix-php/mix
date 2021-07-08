<?php

namespace Mix\Database;

use Mix\Database\Pool\ConnectionPool;
use Mix\Database\Pool\Dialer;
use Mix\ObjectPool\Exception\WaitTimeoutException;

/**
 * Class Database
 * @package Mix\Database
 */
class Database
{

    /**
     * 数据源格式
     * @var string
     */
    protected $dsn = '';

    /**
     * 数据库用户名
     * @var string
     */
    protected $username = 'root';

    /**
     * 数据库密码
     * @var string
     */
    protected $password = '';

    /**
     * 驱动连接选项
     * @var array
     */
    protected $options = [];

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
     * @var Driver
     */
    protected $dialer;

    /**
     * 连接池
     * @var ConnectionPool
     */
    protected $pool;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Driver
     */
    protected $driver;

    /**
     * Database constructor.
     * @param string $dsn
     * @param string $username
     * @param string $password
     * @param array $options
     */
    public function __construct(string $dsn, string $username, string $password, array $options = [])
    {
        $this->dsn = $dsn;
        $this->username = $username;
        $this->password = $password;
        $this->options = $options;

        $this->driver = new Driver(
            $this->dsn,
            $this->username,
            $this->password,
            $this->options
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
                $this->dsn,
                $this->username,
                $this->password,
                $this->options
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
     * @param \Closure $func
     * @return ConnectionInterface
     */
    public function debug(\Closure $func): ConnectionInterface
    {
        return $this->borrow()->debug($func);
    }

    /**
     * @param string $sql
     * @param ...$values
     * @return ConnectionInterface
     */
    public function raw(string $sql, ...$values): ConnectionInterface
    {
        return $this->borrow()->raw($sql, ...$values);
    }

    /**
     * @param string $sql
     * @param ...$values
     * @return ConnectionInterface
     */
    public function exec(string $sql, ...$values): ConnectionInterface
    {
        return $this->borrow()->exec($sql, ...$values);
    }

    /**
     * 插入
     * @param string $table
     * @param array $data
     * @param string $insert
     * @return ConnectionInterface
     */
    public function insert(string $table, array $data, string $insert = 'INSERT INTO'): ConnectionInterface
    {
        return $this->borrow()->insert($table, $data, $insert);
    }

    /**
     * 批量插入
     * @param string $table
     * @param array $data
     * @param string $insert
     * @return ConnectionInterface
     */
    public function batchInsert(string $table, array $data, string $insert = 'INSERT INTO'): ConnectionInterface
    {
        return $this->borrow()->batchInsert($table, $data, $insert);
    }

    /**
     * 自动事务
     * @param \Closure $closure
     * @throws \Throwable
     */
    public function transaction(\Closure $closure)
    {
        $this->borrow()->transaction($closure);
    }

    /**
     * 开始事务
     * @return Transaction
     */
    public function beginTransaction(): Transaction
    {
        return $this->borrow()->beginTransaction();
    }

    /**
     * 启动查询生成器
     * @param string $table
     * @return ConnectionInterface
     */
    public function table(string $table): ConnectionInterface
    {
        return $this->borrow()->table($table);
    }

}
