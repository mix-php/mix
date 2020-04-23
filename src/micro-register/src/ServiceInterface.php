<?php

namespace Mix\Micro\Register;

/**
 * Interface ServiceInterface
 * @package Mix\Micro\Register
 */
interface ServiceInterface extends \JsonSerializable
{

    /**
     * Get name
     * @return string
     */
    public function getName(): string;

    /**
     * Get id
     * @return string
     */
    public function getID(): string;

    /**
     * Get address
     * @return string
     */
    public function getAddress(): string;

    /**
     * Get port
     * @return int
     */
    public function getPort(): int;

}
