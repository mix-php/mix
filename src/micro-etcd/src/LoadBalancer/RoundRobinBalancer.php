<?php

namespace Mix\Micro\Etcd\LoadBalancer;

use Mix\Micro\Etcd\Service\Service;

/**
 * Class RoundRobinBalancer
 * @package Mix\Micro\Etcd\LoadBalancer
 */
class RoundRobinBalancer implements LoadBalancerInterface
{

    /**
     * @var string
     */
    protected $lastID;

    /**
     * Invoke
     * @param Service[id] $services
     * @return Service
     */
    public function invoke(array $services)
    {
        $keys   = array_keys($services);
        $lastID = $this->lastID;
        if (!$lastID) {
            $firstID      = array_shift($keys);
            $this->lastID = $firstID;
            return $services[$firstID];
        }
        if ($lastID && !isset($services[$lastID])) {
            $randomID     = array_rand($services);
            $this->lastID = $randomID;
            return $services[$randomID];
        }
        $nextID = null;
        foreach ($keys as $k => $id) {
            if ($id != $lastID) {
                continue;
            }
            if (isset($keys[$k + 1])) {
                $nextID = $keys[$k + 1];
                break;
            }
            $nextID = array_shift($keys);
        }
        $this->lastID = $nextID;
        return $services[$nextID];
    }

}
