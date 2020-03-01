<?php

namespace Mix\Service;

use Mix\Etcd\Service\Service;

/**
 * Interface DialerInterface
 * @package Mix\Service
 */
interface DialerInterface
{

    /**
     * 拨号
     * @param Service $service
     * @return object
     */
    public function dial(Service $service);

}
