<?php

namespace Mix\SyncInvoke\Pool;

use Mix\Bean\BeanInjector;
use Mix\Pool\DialerInterface;
use Mix\SyncInvoke\Client\Connection;

/**
 * Class Dialer
 * @package Mix\SyncInvoke\Pool
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
     * @return Connection
     * @throws \PhpDocReader\AnnotationException
     * @throws \ReflectionException
     * @throws \Swoole\Exception
     */
    public function dial()
    {
        $conn = new Connection([
            'port'    => $this->port,
            'timeout' => $this->timeout,
        ]);
        $conn->connect();
        return $conn;
    }

}
