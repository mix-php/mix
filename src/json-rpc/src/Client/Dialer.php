<?php

namespace Mix\JsonRpc\Client;

use Mix\Bean\BeanInjector;
use Mix\JsonRpc\Intercept\InterceptorInterface;
use Mix\Micro\Service\Exception\NotFoundException;
use Mix\Micro\Service\RegistryInterface;
use Mix\Micro\Service\ServiceInterface;

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
     * @var InterceptorInterface[]
     */
    public $interceptors = [];

    /**
     * @var RegistryInterface
     */
    public $registry;

    /**
     * @var int
     */
    public $retry = 3;

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
     * @param InterceptorInterface|null $interceptor
     * @return Connection
     * @throws \PhpDocReader\AnnotationException
     * @throws \ReflectionException
     * @throws \Swoole\Exception
     */
    public function dial(string $host, int $port, InterceptorInterface $interceptor = null)
    {
        $interceptors = $this->interceptors;
        $interceptor and array_shift($interceptors, $interceptor);
        $conn = new Connection([
            'host'         => $host,
            'port'         => $port,
            'timeout'      => $this->timeout,
            'callTimeout'  => $this->callTimeout,
            'interceptors' => $interceptors,
        ]);
        $conn->connect();
        return $conn;
    }

    /**
     * Dial from service
     * @param string $name
     * @param InterceptorInterface|null $interceptor
     * @return Connection
     * @throws \PhpDocReader\AnnotationException
     * @throws \ReflectionException
     * @throws \Swoole\Exception
     */
    public function dialFromService(string $name, InterceptorInterface $interceptor = null)
    {
        for ($i = 0; $i < $this->retry; $i++) {
            try {
                $service = $this->registry->service($name);
                $conn    = $this->dial($service->getAddress(), $service->getPort(), $interceptor);
                break;
            } catch (NotFoundException $ex) {
                throw $ex;
            } catch (\Throwable $ex) {
                if ($i == $this->retry - 1) {
                    throw $ex;
                }
            }
        }
        return $conn;
    }

}
