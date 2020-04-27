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
        $client = new Client([
            'port'          => $port,
            'timeout'       => $this->timeout,
            'invokeTimeout' => $this->invokeTimeout,
            'dispatcher'    => $this->dispatcher,
        ]);
        return $client;
    }

}
