<?php

namespace Mix\Micro\Gateway;

use Mix\Http\Message\Response;
use Mix\Http\Message\ServerRequest;
use Mix\Micro\Exception\NotFoundException;
use Mix\Micro\RegistryInterface;
use Mix\Micro\ServiceInterface;

/**
 * Interface ProxyInterface
 * @package Mix\Micro\Gateway
 */
interface ProxyInterface
{

    /**
     * Get handle pattern
     * @return string
     */
    public function pattern();

    /**
     * Get service
     *
     * @param RegistryInterface $registry
     * @param ServerRequest $request
     * @return ServiceInterface
     * @throws NotFoundException
     */
    public function service(RegistryInterface $registry, ServerRequest $request);

    /**
     * Proxy
     * @param ServiceInterface $service
     * @param ServerRequest $request
     * @param Response $response
     * @return int status
     */
    public function proxy(ServiceInterface $service, ServerRequest $request, Response $response);

    /**
     * Close
     */
    public function close();

}
