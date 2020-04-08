<?php

namespace Mix\Micro\Register;

/**
 * Interface ServiceInterface
 * @package Mix\Micro\Register
 */
interface ServiceInterface extends \JsonSerializable
{

    /**
     * Get id
     * @return string
     */
    public function getID();

    /**
     * Get name
     * @return string
     */
    public function getName();

    /**
     * Get address
     * @return string
     */
    public function getAddress();

    /**
     * Get port
     * @return int
     */
    public function getPort();

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
    
    /**
     * Get node
     * @return []string
     */
    public function getNode();

    /**
     * Set node
     * @param string $id
     * @param string $name
     */
    public function withNode(string $id, string $name);

}
