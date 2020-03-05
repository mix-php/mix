<?php

namespace Mix\Etcd\Factory;

use Mix\Etcd\Service\Service;
use Mix\Micro\Helper\ServiceHelper;

/**
 * Class ServiceFactory
 * @package Mix\Etcd\Factory
 */
class ServiceFactory
{

    /**
     * Create service
     * @param string $name
     * @param string $address
     * @param int $port
     * @return Service
     * @throws \Exception
     */
    public function createService(string $name, string $address, int $port): Service
    {
        $id = ServiceHelper::uuid();
        return new Service($id, $name, $address, $port);
    }

    /**
     * Create jsonrpc service
     * @param string $name
     * @param string $address
     * @param int $port
     * @return Service
     * @throws \Exception
     */
    public function createJsonRpcService(string $name, string $address, int $port)
    {
        $service = $this->createService($name, $address, $port);
        $service->withMetadata('transport', 'tcp');
        $service->withMetadata('protocol', 'json-rpc');
        return $service;
    }

    /**
     * Create grpc service
     * @param string $name
     * @param string $address
     * @param int $port
     * @return Service
     * @throws \Exception
     */
    public function createGrpcService(string $name, string $address, int $port)
    {
        $service = $this->createService($name, $address, $port);
        $service->withMetadata('transport', 'http');
        $service->withMetadata('protocol', 'grpc');
        return $service;
    }

}
