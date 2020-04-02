<?php

namespace Mix\Etcd\LoadBalancer;

use Mix\Etcd\Service\Service;

/**
 * Interface LoadBalancerInterface
 * @package Mix\Etcd\LoadBalancer
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
