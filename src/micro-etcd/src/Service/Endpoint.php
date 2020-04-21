<?php

namespace Mix\Micro\Etcd\Service;

use Mix\Micro\Register\EndpointInterface;
use Mix\Micro\Register\RequestInterface;
use Mix\Micro\Register\ResponseInterface;
use Mix\Micro\Register\ValueInterface;

/**
 * Class Endpoint
 * @package Mix\Micro\Etcd\Service
 */
class Endpoint implements EndpointInterface
{

    /**
     * @var string
     */
    protected $name;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var ResponseInterface
     */
    protected $response;

    /**
     * @var string[]
     */
    protected $metadata;

    /**
     * Endpoint constructor.
     * @param string $name
     * @param RequestInterface|null $request
     * @param ResponseInterface|null $response
     */
    public function __construct(string $name, RequestInterface $request = null, ResponseInterface $response = null)
    {
        $this->name     = $name;
        $this->request  = $request;
        $this->response = $response;
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
     * Get request
     * @return ValueInterface
     */
    public function getRequest(): ValueInterface
    {
        return $this->request;
    }

    /**
     * Set request
     * @param ValueInterface $request
     */
    public function withRequest(ValueInterface $request)
    {
        $this->request = $request;
    }

    /**
     * Get request
     * @return ValueInterface
     */
    public function getResponse(): ValueInterface
    {
        return $this->response;
    }

    /**
     * Set response
     * @param ValueInterface $response
     */
    public function withResponse(ValueInterface $response)
    {
        $this->response = $response;
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
