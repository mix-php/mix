<?php

namespace Mix\Micro\Etcd\Service;

/**
 * Class Node
 * @package Mix\Micro\Etcd\Service
 */
class Node implements \JsonSerializable
{

    /**
     * @var string
     */
    protected $id = '';

    /**
     * @var string
     */
    protected $address = '';

    /**
     * @var string[]
     */
    protected $metadata;

    /**
     * Node constructor.
     * @param string $id
     * @param string $address
     */
    public function __construct(string $id, string $address)
    {
        $this->id      = $id;
        $this->address = $address;
    }

    /**
     * Get id
     * @return string
     */
    public function getID(): string
    {
        return $this->id;
    }

    /**
     * Get address
     * @return string
     */
    public function getAddress(): string
    {
        return $this->address;
    }

    /**
     * Get metadata
     * @return []string
     */
    public function getMetadata()
    {
        return $this->metadata;
    }

    /**
     * Add or update metadata
     * @param string $id
     * @param string $name
     */
    public function withMetadata(string $key, string $value)
    {
        $this->metadata[$key] = $value;
    }

    /**
     * Json serialize
     * @return array
     */
    public function jsonSerialize()
    {
        $data = [];
        foreach ($this as $key => $val) {
            $data[$key] = $val;
        }
        return $data;
    }

}
