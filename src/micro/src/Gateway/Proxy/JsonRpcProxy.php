<?php

namespace Mix\Micro\Gateway\Proxy;

use Mix\Http\Message\Response;
use Mix\Http\Message\ServerRequest;
use Mix\Micro\Gateway\ProxyInterface;
use Mix\Micro\ServiceInterface;

/**
 * Class JsonRpcProxy
 * @package Mix\Micro\Gateway\Proxy
 */
class JsonRpcProxy implements ProxyInterface
{

    /**
     * @var string
     */
    public $pattern = '/jsonrpc';

    /**
     * @var string
     */
    public $namespace = 'php.micro.jsonrpc';

    /**
     * Get handle pattern
     * @return string
     */
    public function pattern()
    {
        return $this->pattern;
    }

    /**
     * Get namespace
     * @return string
     */
    public function namespace()
    {
        return $this->namespace;
    }

    /**
     * Proxy
     * @param ServiceInterface $service
     * @param ServerRequest $request
     * @param Response $response
     * @return int status
     */
    public function proxy(ServiceInterface $service, ServerRequest $request, Response $response)
    {
        // TODO: Implement proxy() method.
    }

    /**
     * Close
     */
    public function close()
    {
        // TODO: Implement close() method.
    }

}
