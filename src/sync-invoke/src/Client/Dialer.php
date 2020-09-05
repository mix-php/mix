<?php

namespace Mix\SyncInvoke\Client;

use Mix\Bean\BeanInjector;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * Class Dialer
 * @package Mix\SyncInvoke\Client
 */
class Dialer
{

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
     * 最大连接数
     * @var int
     * @deprecated 废弃，使用 maxOpen 取代
     */
    public $maxActive = -1;

    /**
     * 最大活跃数
     * "0" 为不限制，默认等于cpu数量
     * @var int
     */
    public $maxOpen = -1;

    /**
     * 最多可空闲连接数
     * 默认等于cpu数量
     * @var int
     */
    public $maxIdle = -1;

    /**
     * 事件调度器
     * @var EventDispatcherInterface
     */
    public $dispatcher;

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
     * @param int $port
     * @return Client
     * @throws \PhpDocReader\AnnotationException
     * @throws \ReflectionException
     */
    public function dial(int $port)
    {
        $client             = new Client($port, $this->timeout, $this->invokeTimeout, $this->maxOpen, $this->maxIdle);
        $client->dispatcher = $this->dispatcher;
        return $client;
    }

}
