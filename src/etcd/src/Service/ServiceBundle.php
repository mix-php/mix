<?php

namespace Mix\Etcd\Service;

use Mix\Micro\Register\ServiceInterface;

/**
 * Class ServiceBundle
 * @package Mix\Etcd\Service
 */
class ServiceBundle
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
     * Get service num
     * @return int
     */
    public function count(): int
    {
        return count($this->services);
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
