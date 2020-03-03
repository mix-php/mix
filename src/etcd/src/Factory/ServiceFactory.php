<?php

namespace Mix\Etcd\Factory;

use Mix\Etcd\Service\Service;
use Mix\ServiceCenter\Helper\ServiceHelper;

/**
 * Class ServiceFactory
 * @package Mix\Etcd\Factory
 */
class ServiceFactory
{

    /**
     * Cteate service
     * @param string $name
     * @param string $address
     * @param int $port
     * @return Service
     * @throws \Exception
     */
    public function createService(string $name, string $address, int $port): Service
    {
        $id = ServiceHelper::uuid();
        return new Service($id, $name, $address, $port);
    }

}
