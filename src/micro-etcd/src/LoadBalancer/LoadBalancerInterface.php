<?php

namespace Mix\Micro\Etcd\LoadBalancer;

use Mix\Micro\Etcd\Service\Service;

/**
 * Interface LoadBalancerInterface
 * @package Mix\Micro\Etcd\LoadBalancer
 */
interface LoadBalancerInterface
{

    /**
     * Invoke
     * @param Service[id] $services
     * @return Service
     */
    public function invoke(array $services);

}
