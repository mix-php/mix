<?php

namespace Mix\Etcd\Factory;

use Mix\Etcd\Service\Service;
use Mix\Etcd\Service\ServiceBundle;
use Mix\JsonRpc\Server as JsonRpcServer;
use Mix\Http\Server\Server as HttpServer;
use Mix\Micro\Helper\ServiceHelper;
use Mix\Route\Router;

/**
 * Class ServiceBundleFactory
 * @package Mix\Etcd\Factory
 */
class ServiceBundleFactory
{

    /**
     * Create service bundle
     * @param Service ...$services
     * @return ServiceBundle
     */
    public function createServiceBundle(Service ...$services): ServiceBundle
    {
        return new ServiceBundle(...$services);
    }

    /**
     * Create service bundle form http (api or web)
     * @param HttpServer $server
     * @param Router $router
     * @return ServiceBundle
     * @throws \Exception
     */
    public function createServiceBundleFromHttp(HttpServer $server, Router $router, string $namespace = 'php.micro.api')
    {
        $serviceFactory = new ServiceFactory();
        $serviceBundle  = $this->createServiceBundle();
        foreach ($router->services() as $name) {
            $service = $serviceFactory->createJsonRpcService(
                sprintf('%s.%s', $namespace, $name),
                ServiceHelper::localIP(),
                $server->port
            );
            $serviceBundle->add($service);
        }
        return $serviceBundle;
    }

    /**
     * Create service bundle form json-rpc
     * @param JsonRpcServer $server
     * @param string $namespace
     * @return ServiceBundle
     * @throws \Exception
     */
    public function createServiceBundleFromJsonRpc(JsonRpcServer $server, string $namespace = 'php.micro.srv.jsonrpc')
    {
        $serviceFactory = new ServiceFactory();
        $serviceBundle  = $this->createServiceBundle();
        foreach ($server->services() as $name) {
            $service = $serviceFactory->createJsonRpcService(
                sprintf('%s.%s', $namespace, $name),
                ServiceHelper::localIP(),
                $server->port
            );
            $serviceBundle->add($service);
        }
        return $serviceBundle;
    }

}
