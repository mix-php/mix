<?php

namespace Mix\Etcd\Factory;

use Mix\Etcd\Service\Service;
use Mix\Etcd\Service\ServiceBundle;

/**
 * Class ServiceBundleFactory
 * @package Mix\Etcd\Factory
 */
class ServiceBundleFactory
{

    /**
     * Create service bundle
     * @param Service ...$services
     * @return ServiceBundle
     */
    public function createServiceBundle(Service ...$services): ServiceBundle
    {
        return new ServiceBundle(...$services);
    }

}
