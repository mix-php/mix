<?php

namespace Mix\Micro;

/**
 * Interface ServiceBundleInterface
 * @package Mix\Micro
 */
interface ServiceBundleInterface
{

    /**
     * Get all service
     * @return ServiceInterface[]
     */
    public function items();

    /**
     * Add service
     * @param ServiceInterface $service
     */
    public function add(ServiceInterface $service);

}
