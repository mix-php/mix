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
     * @return string|null
     */
    public function getVersion(): ?string;

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
     * Get endpoints
     * @return EndpointInterface[]
     */
    public function getEndpoints();

    /**
     * Add endpoint
     * @param EndpointInterface $endpoint
     */
    public function withEndpoint(EndpointInterface $endpoint);

    /**
     * Get nodes
     * @return NodeInterface[]
     */
    public function getNodes();

    /**
     * Set node
     * @param NodeInterface $node
     */
    public function withNode(NodeInterface $node);

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
