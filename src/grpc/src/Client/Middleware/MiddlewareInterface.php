<?php

namespace Mix\Grpc\Middleware;

use Mix\Grpc\Client\Parameters;

/**
 * Interface MiddlewareInterface
 * @package Mix\Grpc\Middleware
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
