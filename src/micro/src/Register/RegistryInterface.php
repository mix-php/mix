<?php

namespace Mix\Micro\Register;

use Mix\Micro\Register\Exception\NotFoundException;

/**
 * Interface RegistryInterface
 * @package Mix\Micro\Register
 */
interface RegistryInterface
{

    /**
     * Get Service
     * @param string $name
     * @return ServiceInterface
     * @throws NotFoundException
     */
    public function service(string $name): ServiceInterface;

    /**
     * Register
     * @param ServiceBundleInterface $bundle
     * @throws \Exception
     */
    public function register(ServiceBundleInterface $bundle);

    /**
     * Un Register
     * @param ServiceBundleInterface $bundle
     * @throws \Exception
     */
    public function unregister(ServiceBundleInterface $bundle);

    /**
     * Close
     * close all monitor
     * unregister all service
     */
    public function close();

}
