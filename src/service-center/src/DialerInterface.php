<?php

namespace Mix\ServiceCenter;

/**
 * Interface DialerInterface
 * @package Mix\ServiceCenter
 */
interface DialerInterface
{

    /**
     * Dial
     * @param ServiceInterface $service
     * @return object
     */
    public function dial(ServiceInterface $service);

}
