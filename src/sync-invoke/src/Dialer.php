<?php

namespace Mix\SyncInvoke;

use Mix\Bean\BeanInjector;

/**
 * Class Dialer
 * @package Mix\SyncInvoke
 */
class Dialer
{

    /**
     * @var int
     */
    public $port = 0;

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
     * @throws \Swoole\Exception
     */
    public function dial()
    {
        return new Connection($this->port, $this->timeout);
    }

}
