<?php

namespace Mix\Micro\Etcd\LoadBalancer;

use Mix\Micro\Etcd\Service\Service;

/**
 * Class RandomBalancer
 * @package Mix\Micro\Etcd\LoadBalancer
 */
class RandomBalancer implements LoadBalancerInterface
{

    /**
     * Invoke
     * @param Service[id] $services
     * @return Service
     */
    public function invoke(array $services)
    {
        return $services[array_rand($services)];
    }

}
