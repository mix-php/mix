<?php

namespace Mix\Micro;

use Mix\Micro\Exception\NotFoundException;

/**
 * Interface RegistryInterface
 * @package Mix\Micro
 */
interface RegistryInterface
{

    /**
     * Get Service
     * @param string $name
     * @return ServiceInterface
     * @throws NotFoundException
     */
    public function get(string $name): ServiceInterface;

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
     * Clear
     * close all monitor
     * unregister all service
     */
    public function clear();

}
