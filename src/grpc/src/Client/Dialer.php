<?php

namespace Mix\Grpc\Client;

use Grpc\ChannelCredentials;
use Mix\Bean\BeanInjector;
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
     * @var \Closure
     */
    public $factory;

    /**
     * Global timeout
     * @var float
     */
    public $timeout = 5.0;

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
     * Get default factory
     * @return \Closure
     */
    protected function defaultFactory()
    {
        return function (string $class, string $hostname) {
            return new $class($hostname, [
                'credentials' => ChannelCredentials::createInsecure(),
            ]);
        };
    }

    /**
     * Dial
     * @param string $host
     * @param int $port
     * @param string $class
     * @param MiddlewareInterface|null $middleware
     * @return Proxy
     * @throws \PhpDocReader\AnnotationException
     * @throws \ReflectionException
     */
    public function dial(string $host, int $port, string $class, MiddlewareInterface $middleware = null)
    {
        $middleware and array_unshift($this->middleware, $middleware);
        $factory = $this->factory ?? $this->defaultFactory();
        $client  = call_user_func($factory, $class, sprintf('%s:%d', $host, $port));
        return new Proxy([
            'client'     => $client,
            'timeout'    => $this->timeout,
            'middleware' => $this->middleware,
        ]);
    }

    /**
     * Dial from service
     * @param string $name
     * @param string $class
     * @param MiddlewareInterface|null $middleware
     * @return Proxy
     * @throws \PhpDocReader\AnnotationException
     * @throws \ReflectionException
     */
    public function dialFromService(string $name, string $class, MiddlewareInterface $middleware = null)
    {
        $service = $this->registry->service($name);
        return $this->dial($service->getAddress(), $service->getPort(), $class, $middleware);
    }

}
