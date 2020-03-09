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
     * Get service num
     * @return int
     */
    public function count();

    /**
     * Add service
     * @param ServiceInterface $service
     */
    public function add(ServiceInterface $service);

}
