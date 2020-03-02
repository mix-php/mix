<?php

namespace Mix\Etcd\Service;

/**
 * Class ServiceBundle
 * @package Mix\Etcd\Service
 */
class ServiceBundle
{

    /**
     * @var Service[]
     */
    protected $services = [];

    /**
     * ServiceBundle constructor.
     * @param Service ...$services
     */
    public function __construct(Service ...$services)
    {
        $this->services = $services;
    }

    /**
     * Get all service
     * @return Service[]
     */
    public function items()
    {
        return $this->services;
    }
 
    /**
     * Add service
     * @param Service $service
     */
    public function add(Service $service)
    {
        array_push($this->services, $service);
    }

}
