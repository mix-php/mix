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
    public function services()
    {
        return $this->services;
    }

}
