<?php

namespace Mix\Micro\Etcd\Service;

use Mix\Micro\Register\EndpointInterface;
use Mix\Micro\Register\NodeInterface;
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
     * @var EndpointInterface[]
     */
    protected $endpoints = [];

    /**
     * @var NodeInterface[]
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
     * @return EndpointInterface[]
     */
    public function getEndpoints()
    {
        return $this->endpoints;
    }

    /**
     * Add endpoint
     * @param EndpointInterface $endpoint
     */
    public function withEndpoint(EndpointInterface $endpoint)
    {
        $this->endpoints[] = $endpoint;
    }

    /**
     * Get nodes
     * @return NodeInterface[]
     */
    public function getNodes()
    {
        return $this->nodes;
    }

    /**
     * Set node
     * @param NodeInterface $node
     */
    public function withNode(NodeInterface $node)
    {
        $this->nodes[] = $node;
    }

    /**
     * Get node
     * @return NodeInterface
     */
    public function getFirstNode(): NodeInterface
    {
        return current($this->nodes);
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
