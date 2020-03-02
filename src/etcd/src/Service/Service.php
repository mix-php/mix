<?php

namespace Mix\Etcd\Service;

use Mix\ServiceCenter\ServiceInterface;

/**
 * Class Service
 * @package Mix\Etcd\Service
 */
class Service implements ServiceInterface
{

    /**
     * @var string
     */
    protected $id = '';

    /**
     * @var string
     */
    protected $name = '';

    /**
     * @var string
     */
    protected $address = '';

    /**
     * @var int
     */
    protected $port = 0;

    /**
     * @var string[]
     */
    protected $metadata = [];

    /**
     * @var string[]
     */
    protected $node = [];

    /**
     * Service constructor.
     * @param string $id
     * @param string $name
     * @param string $address
     * @param int $port
     */
    public function __construct(string $id, string $name, string $address, int $port)
    {
        $this->id      = $id;
        $this->name    = $name;
        $this->address = $address;
        $this->port    = $port;
    }

    /**
     * Get id
     * @return string
     */
    public function getID()
    {
        return $this->id;
    }

    /**
     * Get name
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get address
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Get port
     * @return int
     */
    public function getPort()
    {
        return $this->port;
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
     * Get node
     * @return []string
     */
    public function getNode()
    {
        return $this->node;
    }

    /**
     * Set node
     * @param string $id
     * @param string $name
     */
    public function withNode(string $id, string $name)
    {
        $this->node = [
            'id'   => $id,
            'name' => $name,
        ];
    }

    /**
     * Json serialize
     * @return array
     */
    public function jsonSerialize()
    {
        {
            $data = [];
            foreach ($this as $key => $val) {
                $data[$key] = $val;
            }
            return $data;
        }
    }

}
