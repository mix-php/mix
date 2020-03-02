<?php

namespace Mix\Etcd\Node;

use Mix\ServiceCenter\NodeInterface;

/**
 * Class Node
 * @package Mix\Etcd\Node
 */
class Node implements NodeInterface
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
     * @var string[]
     */
    protected $services = [];

    /**
     * Node constructor.
     * @param string $id
     * @param string $name
     * @param string $address
     */
    public function __construct(string $id, string $name, string $address)
    {
        $this->id      = $id;
        $this->name    = $name;
        $this->address = $address;
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
     * Get services
     * @return string[]
     */
    public function getServices()
    {
        return $this->services;
    }

    /**
     * Append service
     * @param string $id
     * @param string $name
     */
    public function withAddedService(string $id, string $name)
    {
        $this->services[] = [
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
