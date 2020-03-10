<?php

namespace Mix\SyncInvoke\Client;

use Mix\Bean\BeanInjector;

/**
 * Class Dialer
 * @package Mix\SyncInvoke\Client
 */
class Dialer
{

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
     * @param int $port
     * @return Connection
     * @throws \PhpDocReader\AnnotationException
     * @throws \ReflectionException
     * @throws \Swoole\Exception
     */
    public function dial(int $port)
    {
        $conn = new Connection([
            'port'    => $port,
            'timeout' => $this->timeout,
        ]);
        $conn->connect();
        return $conn;
    }

}
