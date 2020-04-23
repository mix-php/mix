<?php

namespace Mix\Micro\Etcd\Service;

use Mix\Micro\Register\ServiceInterface;

/**
 * Class Service
 * @package Mix\Micro\Etcd\Service
 */
class Service implements ServiceInterface
{

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $version;

    /**
     * @var string[]
     */
    protected $metadata;

    /**
     * @var Endpoint[]
     */
    protected $endpoints = [];

    /**
     * @var Node[]
     */
    protected $nodes = [];

    /**
     * Service constructor.
     * @param string $name
     * @param string|null $version
     */
    public function __construct(string $name, string $version = null)
    {
        $this->name    = $name;
        $this->version = $version;
    }

    /**
     * Get name
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get version
     * @return string|null
     */
    public function getVersion(): ?string
    {
        return $this->version;
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
     * Get endpoints
     * @return Endpoint[]
     */
    public function getEndpoints()
    {
        return $this->endpoints;
    }

    /**
     * Add endpoint
     * @param Endpoint $endpoint
     */
    public function withEndpoint(Endpoint $endpoint)
    {
        $this->endpoints[] = $endpoint;
    }

    /**
     * Get nodes
     * @return Node[]
     */
    public function getNodes()
    {
        return $this->nodes;
    }

    /**
     * Set node
     * @param Node $node
     */
    public function withNode(Node $node)
    {
        $this->nodes[] = $node;
    }

    /**
     * Get node
     * @return Node
     */
    public function getFirstNode(): Node
    {
        return current($this->getNodes());
    }

    /**
     * Get id
     * @return string
     */
    public function getID(): string
    {
        return $this->getFirstNode()->getID();
    }

    /**
     * Get address
     * @return string
     */
    public function getAddress(): string
    {
        $node = $this->getFirstNode();
        list($address) = explode(':', $node->getAddress());
        return $address;
    }

    /**
     * Get port
     * @return int
     */
    public function getPort(): int
    {
        $node = $this->getFirstNode();
        list(, $port) = explode(':', $node->getAddress());
        return (int)$port;
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
