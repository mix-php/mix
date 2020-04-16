<?php

namespace Mix\Micro\Register;

/**
 * Interface ResponseFactoryInterface
 * @package Mix\Micro\Register
 */
interface ResponseFactoryInterface
{

    /**
     * Create response
     * @param \ReflectionClass $class
     * @return ResponseInterface
     */
    public function createResponse(\ReflectionClass $class): ResponseInterface;

}
