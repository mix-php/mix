<?php

namespace Mix\SyncInvoke\Client\Pool;

use Mix\Bean\BeanInjector;
use Mix\ObjectPool\DialerInterface;
use Mix\SyncInvoke\Client\Connection;
use Mix\SyncInvoke\Client\Driver;

/**
 * Class Dialer
 * @package Mix\SyncInvoke\Client\Pool
 */
class Dialer implements DialerInterface
{

    /**
     * @var int
     */
    public $port;

    /**
     * @var float
     */
    public $timeout = 5.0;

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
     * @return Driver
     * @throws \Swoole\Exception
     */
    public function dial()
    {
        return new Driver($this->port, $this->timeout);
    }

}
