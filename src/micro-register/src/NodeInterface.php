<?php

namespace Mix\Micro\Register;

/**
 * Interface NodeInterface
 * @package Mix\Micro\Register
 */
interface NodeInterface extends \JsonSerializable
{

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
     * Get metadata
     * @return []string
     */
    public function getMetadata();

    /**
     * Add or update metadata
     * @param string $id
     * @param string $name
     */
    public function withMetadata(string $key, string $value);

}
