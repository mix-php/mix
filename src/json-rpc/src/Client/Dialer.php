<?php

namespace Mix\JsonRpc\Client;

use Mix\Bean\BeanInjector;
use Mix\JsonRpc\Middleware\MiddlewareInterface;
use Mix\Micro\Register\Exception\NotFoundException;
use Mix\Micro\Register\RegistryInterface;
use Mix\Micro\Register\ServiceInterface;

/**
 * Class Dialer
 * @package Mix\JsonRpc\Client
 */
class Dialer
{

    /**
     * Global timeout
     * @var float
     */
    public $timeout = 5.0;

    /**
     * Call timeout
     * @var float
     */
    public $callTimeout = 10.0;

    /**
     * @var array MiddlewareInterface class or object
     */
    public $middleware = [];

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
     * @param MiddlewareInterface|null $middleware
     * @return Connection
     * @throws \PhpDocReader\AnnotationException
     * @throws \ReflectionException
     * @throws \Swoole\Exception
     */
    public function dial(string $host, int $port, MiddlewareInterface $middleware = null)
    {
        $middleware and array_unshift($this->middleware, $middleware);
        $conn = new Connection([
            'host'        => $host,
            'port'        => $port,
            'timeout'     => $this->timeout,
            'callTimeout' => $this->callTimeout,
            'middleware'  => $this->middleware,
        ]);
        $conn->connect();
        return $conn;
    }

    /**
     * Dial from service
     * @param string $name
     * @param MiddlewareInterface|null $middleware
     * @return Connection
     * @throws \PhpDocReader\AnnotationException
     * @throws \ReflectionException
     * @throws \Swoole\Exception
     */
    public function dialFromService(string $name, MiddlewareInterface $middleware = null)
    {
        $service = $this->registry->service($name);
        return $this->dial($service->getAddress(), $service->getPort(), $middleware);
    }

}
