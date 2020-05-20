<?php

namespace Mix\Micro\Register;

use Mix\Micro\Options;
use Mix\Micro\Register\Exception\NotFoundException;

/**
 * Interface RegistryInterface
 * @package Mix\Micro\Register
 */
interface RegistryInterface
{

    /**
     * Extract
     * @param Options $options
     * @return ServiceInterface[]
     */
    public function extract(Options $options);
    
    /**
     * Get Service
     * @param string $name
     * @return ServiceInterface
     * @throws NotFoundException
     */
    public function service(string $name): ServiceInterface;

    /**
     * Register
     * @param ServiceInterface ...$service
     * @throws \Exception
     */
    public function register(ServiceInterface ...$service);

    /**
     * Un Register
     * @param ServiceInterface ...$service
     * @throws \Exception
     */
    public function deregister(ServiceInterface ...$service);

    /**
     * Close
     * close all monitor
     * deregister all service
     */
    public function close();

}
