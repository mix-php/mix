<?php

namespace Mix\Etcd\Factory;

use Mix\Etcd\Service\Service;
use Mix\Micro\Service\Helper\ServiceHelper;
use Mix\Micro\Service\ServiceFactoryInterface;
use Mix\Micro\Service\ServiceInterface;

/**
 * Class ServiceFactory
 * @package Mix\Etcd\Factory
 */
class ServiceFactory implements ServiceFactoryInterface
{

    /**
     * Create service
     * @param string $name
     * @param string $address
     * @param int $port
     * @return ServiceInterface
     * @throws \Exception
     */
    public function createService(string $name, string $address, int $port): ServiceInterface
    {
        $id = ServiceHelper::uuid();
        return new Service($id, $name, $address, $port);
    }

    /**
     * Create api service
     * @param string $name
     * @param string $address
     * @param int $port
     * @return ServiceInterface
     * @throws \Exception
     */
    public function createApiService(string $name, string $address, int $port): ServiceInterface
    {
        $service = $this->createService($name, $address, $port);
        $service->withMetadata('transport', 'http');
        $service->withMetadata('protocol', 'json');
        $service->withMetadata('type', 'api');
        return $service;
    }

    /**
     * Create web service
     * @param string $name
     * @param string $address
     * @param int $port
     * @return ServiceInterface
     * @throws \Exception
     */
    public function createWebService(string $name, string $address, int $port): ServiceInterface
    {
        $service = $this->createService($name, $address, $port);
        $service->withMetadata('transport', 'http');
        $service->withMetadata('protocol', 'html');
        $service->withMetadata('type', 'api');
        return $service;
    }

    /**
     * Create jsonrpc service
     * @param string $name
     * @param string $address
     * @param int $port
     * @return ServiceInterface
     * @throws \Exception
     */
    public function createJsonRpcService(string $name, string $address, int $port): ServiceInterface
    {
        $service = $this->createService($name, $address, $port);
        $service->withMetadata('transport', 'tcp');
        $service->withMetadata('protocol', 'json');
        $service->withMetadata('type', 'jsonrpc');
        return $service;
    }

    /**
     * Create grpc service
     * @param string $name
     * @param string $address
     * @param int $port
     * @return ServiceInterface
     * @throws \Exception
     */
    public function createGrpcService(string $name, string $address, int $port): ServiceInterface
    {
        $service = $this->createService($name, $address, $port);
        $service->withMetadata('transport', 'http');
        $service->withMetadata('protocol', 'protobuf');
        $service->withMetadata('type', 'grpc');
        return $service;
    }

}
