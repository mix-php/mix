<?php

namespace Mix\Etcd\Factory;

use Mix\Etcd\Service\Service;
use Mix\Etcd\Service\ServiceBundle;
use Mix\Grpc\Server as GrpcServer;
use Mix\JsonRpc\Server as JsonRpcServer;
use Mix\Http\Server\Server as HttpServer;
use Mix\Micro\Register\Helper\ServiceHelper;
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


}
