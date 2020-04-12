<?php

namespace Mix\Grpc\Client\Middleware;

/**
 * Interface MiddlewareInterface
 * @package Mix\Grpc\Client\Middleware
 */
interface MiddlewareInterface
{

    /**
     * Process
     * @param Parameters $parameters
     * @param RequestHandler $handler
     * @return object
     */
    public function process(Parameters $parameters, RequestHandler $handler): object;

}
