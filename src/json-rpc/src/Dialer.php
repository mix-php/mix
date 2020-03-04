<?php

namespace Mix\JsonRpc;

use Mix\Bean\BeanInjector;

/**
 * Class Dialer
 * @package Mix\JsonRpc
 */
class Dialer
{

    /**
     * @var string
     */
    public $host = '';

    /**
     * @var int
     */
    public $port = 0;

    /**
     * @var float
     */
    public $timeout = 3.0;

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
     * @param string $host
     * @param int $port
     * @return Connection
     * @throws \Swoole\Exception
     */
    public function dial(string $host = '', int $port = 0)
    {
        $host = $host ?: $this->host;
        $port = $port ?: $this->port;
        return new Connection($host, $port, $this->timeout);
    }

}
