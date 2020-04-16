<?php

namespace Mix\Micro\Register;

/**
 * Interface RequestFactoryInterface
 * @package Mix\Micro\Register
 */
interface RequestFactoryInterface
{

    /**
     * Create request
     * @param \ReflectionParameter $parameter
     * @return RequestInterface
     */
    public function createRequest(\ReflectionParameter $parameter): RequestInterface;

}
