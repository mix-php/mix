<?php

namespace Mix\Micro\Register;

/**
 * Interface ServiceFactoryInterface
 * @package Mix\Micro\Register
 */
interface ServiceFactoryInterface
{


    /**
     * Create service
     * @param string $name
     * @param string $address
     * @param int $port
     * @return ServiceInterface
     */
    public function createService(string $name, string $address, int $port): ServiceInterface;

    /**
     * Create api service
     * @param string $name
     * @param string $address
     * @param int $port
     * @return ServiceInterface
     */
    public function createApiService(string $name, string $address, int $port): ServiceInterface;

    /**
     * Create web service
     * @param string $name
     * @param string $address
     * @param int $port
     * @return ServiceInterface
     */
    public function createWebService(string $name, string $address, int $port): ServiceInterface;

    /**
     * Create jsonrpc service
     * @param string $name
     * @param string $address
     * @param int $port
     * @return ServiceInterface
     */
    public function createJsonRpcService(string $name, string $address, int $port): ServiceInterface;

    /**
     * Create grpc service
     * @param string $name
     * @param string $address
     * @param int $port
     * @return ServiceInterface
     */
    public function createGrpcService(string $name, string $address, int $port): ServiceInterface;

}
