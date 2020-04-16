<?php

namespace Mix\Micro\Register;

/**
 * Interface EndpointFactoryInterface
 * @package Mix\Micro\Register
 */
interface EndpointFactoryInterface
{

    /**
     * Create endpoint
     * @param string $name
     * @return EndpointInterface
     */
    public function createEndpoint(string $name): EndpointInterface;

}
