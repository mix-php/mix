<?php

namespace Mix\Micro\Etcd\Factory;

use Mix\Micro\Etcd\Service\Endpoint;

/**
 * Class EndpointFactory
 * @package Mix\Micro\Etcd\Factory
 */
class EndpointFactory
{

    /**
     * Create endpoint
     * @param string $name
     * @return Endpoint
     */
    public function createEndpoint(string $name): Endpoint
    {
        return new Endpoint($name);
    }

}
