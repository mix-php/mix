<?php

namespace Mix\JsonRpc\Client;

use Mix\Bean\BeanInjector;
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
     * @var int
     */
    public $retry = 3;

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
            'host'        => $host,
            'port'        => $port,
            'timeout'     => $this->timeout,
            'callTimeout' => $this->callTimeout,
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
     * @throws NotFoundException
     */
    public function dialFromService(string $name)
    {
        for ($i = 0; $i < $this->retry; $i++) {
            try {
                $service = $this->registry->get($name);
                $conn    = new Connection([
                    'host'        => $service->getAddress(),
                    'port'        => $service->getPort(),
                    'timeout'     => $this->timeout,
                    'callTimeout' => $this->callTimeout,
                ]);
                $conn->connect();
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
