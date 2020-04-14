<?php

namespace Mix\Grpc\Client\Middleware;

use Mix\Grpc\Client\Message\Request;

/**
 * Interface MiddlewareInterface
 * @package Mix\Grpc\Client\Middleware
 */
interface MiddlewareInterface
{

    /**
     * Process
     * @param Request $parameters
     * @param RequestHandler $handler
     * @return object
     */
    public function process(Request $request, RequestHandler $handler): object;

}
