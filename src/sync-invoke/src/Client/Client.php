<?php

namespace Mix\SyncInvoke\Client;

use Mix\Bean\BeanInjector;
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
    public $port;

    /**
     * Global timeout
     * @var float
     */
    public $timeout = 5.0;

    /**
     * Invoke timeout
     * @var float
     */
    public $invokeTimeout = 10.0;

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
     * Connection constructor.
     * @param array $config
     * @throws \PhpDocReader\AnnotationException
     * @throws \ReflectionException
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
                'port'    => $this->port,
                'timeout' => $this->timeout,
            ]),
            'dispatcher' => $this->dispatcher,
        ]);
        $this->pool = $pool;
    }

    /**
     * Borrow connection
     * @return Connection
     */
    public function borrow(): Connection
    {
        $driver              = $this->pool->borrow();
        $conn                = new Connection($driver);
        $conn->invokeTimeout = $this->invokeTimeout;
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
