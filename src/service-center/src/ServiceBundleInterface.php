<?php

namespace Mix\ServiceCenter;

/**
 * Interface ServiceBundleInterface
 * @package Mix\ServiceCenter
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
