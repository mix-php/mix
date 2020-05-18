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
     */
    public $maxActive = 5;

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
     * @var ConnectionPool
     */
    protected $pool;

    /**
     * Client constructor.
     * @param int $port
     * @param float $timeout
     * @param float $invokeTimeout
     * @throws \PhpDocReader\AnnotationException
     * @throws \ReflectionException
     */
    public function __construct(int $port, float $timeout = 5.0, float $invokeTimeout = 10.0)
    {
        $this->port          = $port;
        $this->timeout       = $timeout;
        $this->invokeTimeout = $invokeTimeout;

        $pool             = new ConnectionPool([
            'dialer' => new Dialer([
                'port'    => $this->port,
                'timeout' => $this->timeout,
            ]),
        ]);
        $pool->maxActive  = &$this->maxActive;
        $pool->maxIdle    = &$this->maxIdle;
        $pool->dispatcher = &$this->dispatcher;
        $this->pool       = $pool;
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
