<?php

namespace Mix\Etcd\Service;

use Mix\ServiceCenter\ServiceBundleInterface;
use Mix\ServiceCenter\ServiceInterface;

/**
 * Class ServiceBundle
 * @package Mix\Etcd\Service
 */
class ServiceBundle implements ServiceBundleInterface
{

    /**
     * @var ServiceInterface[]
     */
    protected $services = [];

    /**
     * ServiceBundle constructor.
     * @param ServiceInterface ...$services
     */
    public function __construct(ServiceInterface ...$services)
    {
        $this->services = $services;
    }

    /**
     * Get all service
     * @return ServiceInterface[]
     */
    public function items()
    {
        return $this->services;
    }

    /**
     * Add service
     * @param ServiceInterface $service
     */
    public function add(ServiceInterface $service)
    {
        array_push($this->services, $service);
    }

}
