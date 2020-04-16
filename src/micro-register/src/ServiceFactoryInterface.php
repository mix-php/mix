<?php

namespace Mix\Micro\Register;

/**
 * Interface ServiceFactoryInterface
 * @package Mix\Micro\Register
 */
interface ServiceFactoryInterface
{

    /**
     * Create service
     * @param string $name
     * @param string|null $version
     * @return ServiceInterface
     */
    public function createService(string $name, ?string $version): ServiceInterface;

}
