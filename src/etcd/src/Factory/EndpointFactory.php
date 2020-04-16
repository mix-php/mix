<?php

namespace Mix\Etcd\Factory;

use Mix\Etcd\Service\Endpoint;
use Mix\Micro\Register\EndpointFactoryInterface;
use Mix\Micro\Register\EndpointInterface;

/**
 * Class EndpointFactory
 * @package Mix\Etcd\Factory
 */
class EndpointFactory implements EndpointFactoryInterface
{

    /**
     * Create endpoint
     * @param string $name
     * @return EndpointInterface
     */
    public function createEndpoint(string $name): EndpointInterface
    {
        return new Endpoint($name);
    }

}
