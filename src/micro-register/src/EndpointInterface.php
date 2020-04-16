<?php

namespace Mix\Micro\Register;

/**
 * Interface EndpointInterface
 * @package Mix\Micro\Register
 */
interface EndpointInterface extends \JsonSerializable
{

    /**
     * Get name
     * @return string
     */
    public function getName(): string;

    /**
     * Get request
     * @return ValueInterface
     */
    public function getRequest(): ValueInterface;

    /**
     * Set request
     * @param ValueInterface $request
     */
    public function withRequest(ValueInterface $request);

    /**
     * Get request
     * @return ValueInterface
     */
    public function getResponse(): ValueInterface;

    /**
     * Set response
     * @param ValueInterface $response
     */
    public function withResponse(ValueInterface $response);

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
