<?php

namespace Mix\SyncInvoke\Client;

use Mix\SyncInvoke\Client\Pool\ConnectionPool;
use Mix\SyncInvoke\Client\Pool\Dialer;
use Mix\SyncInvoke\Exception\InvokeException;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * Class Client
 * @package Mix\SyncInvoke\Client
 */
class Client
{

    /**
     * Port
     * @var int
     */
    protected $port;

    /**
     * Global timeout
     * @var float
     */
    protected $timeout = 5.0;

    /**
     * Invoke timeout
     * @var float
     */
    protected $invokeTimeout = 10.0;

    /**
     * 最大连接数
     * @var int
     * @deprecated 废弃，使用 maxOpen 取代
     */
    public $maxActive = -1;

    /**
     * 最大活跃数
     * "0" 为不限制，默认等于cpu数量
     * @var int
     * @deprecated 应该设置为 protected，为了向下兼容而保留 public
     */
    public $maxOpen = -1;

    /**
     * 最多可空闲连接数
     * 默认等于cpu数量
     * @var int
     * @deprecated 应该设置为 protected，为了向下兼容而保留 public
     */
    public $maxIdle = -1;

    /**
     * 连接可复用的最长时间
     * "0" 为不限制
     * @var int
     * @deprecated 应该设置为 protected，为了向下兼容而保留 public
     */
    public $maxLifetime = 0;

    /**
     * 等待新连接超时时间
     * "0" 为不限制
     * @var float
     * @deprecated 应该设置为 protected，为了向下兼容而保留 public
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
     * Client constructor.
     * @param int $port
     * @param float $timeout
     * @param float $invokeTimeout
     * @param int $maxOpen
     * @param int $maxIdle
     * @param int $maxLifetime
     * @param float $waitTimeout
     * @throws \PhpDocReader\AnnotationException
     * @throws \ReflectionException
     */
    public function __construct(int $port, float $timeout = 5.0, float $invokeTimeout = 10.0,
                                int $maxOpen = -1, int $maxIdle = -1, int $maxLifetime = 0, float $waitTimeout = 0.0)
    {
        $this->port          = $port;
        $this->timeout       = $timeout;
        $this->invokeTimeout = $invokeTimeout;
        $this->maxOpen       = $maxOpen;
        $this->maxIdle       = $maxIdle;
        $this->maxLifetime   = $maxLifetime;
        $this->waitTimeout   = $waitTimeout;
        $this->pool          = $this->createPool();
    }

    /**
     * @return ConnectionPool
     * @throws \PhpDocReader\AnnotationException
     * @throws \ReflectionException
     */
    protected function createPool()
    {
        $pool             = new ConnectionPool(
            new Dialer([
                'port'    => $this->port,
                'timeout' => $this->timeout,
            ]),
            $this->maxOpen,
            $this->maxIdle,
            $this->maxLifetime,
            $this->waitTimeout
        );
        $pool->dispatcher = &$this->dispatcher;
        return $pool;
    }

    /**
     * @param int $maxOpen
     * @throws \PhpDocReader\AnnotationException
     * @throws \ReflectionException
     */
    public function setMaxOpen(int $maxOpen)
    {
        $this->maxOpen = $maxOpen;
        $this->pool    = $this->createPool();
    }

    /**
     * @param int $maxIdle
     * @throws \PhpDocReader\AnnotationException
     * @throws \ReflectionException
     */
    public function setMaxIdle(int $maxIdle)
    {
        $this->maxIdle = $maxIdle;
        $this->pool    = $this->createPool();
    }

    /**
     * @param int $maxLifetime
     * @throws \PhpDocReader\AnnotationException
     * @throws \ReflectionException
     */
    public function setMaxLifetime(int $maxLifetime)
    {
        $this->maxLifetime = $maxLifetime;
        $this->pool        = $this->createPool();
    }

    /**
     * @param float $waitTimeout
     * @throws \PhpDocReader\AnnotationException
     * @throws \ReflectionException
     */
    public function setWaitTimeout(float $waitTimeout)
    {
        $this->waitTimeout = $waitTimeout;
        $this->pool        = $this->createPool();
    }

    /**
     * Borrow connection
     * @return Connection
     */
    public function borrow(): Connection
    {
        $driver           = $this->pool->borrow();
        $conn             = new Connection($driver, $this->invokeTimeout);
        $conn->dispatcher = $this->dispatcher;
        return $conn;
    }

    /**
     * Invoke
     * @param \Closure $closure
     * @return mixed
     * @throws InvokeException
     * @throws \Swoole\Exception
     */
    public function invoke(\Closure $closure)
    {
        return $this->borrow()->invoke($closure);
    }

}
