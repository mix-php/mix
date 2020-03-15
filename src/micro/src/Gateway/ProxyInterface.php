<?php

namespace Mix\Micro\Gateway;

use Mix\Http\Message\Response;
use Mix\Http\Message\ServerRequest;
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
     * Get namespace
     * @return string
     */
    public function namespace();

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
