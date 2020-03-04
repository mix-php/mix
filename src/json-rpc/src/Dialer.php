<?php

namespace Mix\JsonRpc;

use Mix\Bean\BeanInjector;
use Mix\ServiceCenter\ServiceInterface;

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
     * @param string $host
     * @param int $port
     * @return Connection
     * @throws \Swoole\Exception
     */
    public function dial()
    {
        return new Connection($this->host, $this->port, $this->timeout);
    }

    /**
     * Dial by service
     * @param ServiceInterface $service
     * @return Connection
     * @throws \Swoole\Exception
     */
    public function dialService(ServiceInterface $service)
    {
        return new Connection($service->getAddress(), $service->getPort(), $this->timeout);
    }

}
