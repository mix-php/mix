<?php

namespace Mix\JsonRpc\Client;

use Mix\Bean\BeanInjector;
use Mix\Micro\RegistryInterface;
use Mix\Micro\ServiceInterface;

/**
 * Class Dialer
 * @package Mix\JsonRpc\Client
 */
class Dialer
{

    /**
     * @var float
     */
    public $timeout = 5.0;

    /**
     * @var RegistryInterface
     */
    public $registry;

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
     * @throws \PhpDocReader\AnnotationException
     * @throws \ReflectionException
     * @throws \Swoole\Exception
     */
    public function dial(string $host, int $port)
    {
        $conn = new Connection([
            'host'    => $host,
            'port'    => $port,
            'timeout' => $this->timeout,
        ]);
        $conn->connect();
        return $conn;
    }

    /**
     * Dial from service
     * @param string $name
     * @return Connection
     * @throws \PhpDocReader\AnnotationException
     * @throws \ReflectionException
     * @throws \Swoole\Exception
     */
    public function dialFromService(string $name)
    {
        $service = $this->registry->get($name);
        $conn    = new Connection([
            'host'    => $service->getAddress(),
            'port'    => $service->getPort(),
            'timeout' => $this->timeout,
        ]);
        $conn->connect();
        return $conn;
    }

}
