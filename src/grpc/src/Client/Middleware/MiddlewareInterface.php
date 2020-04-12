<?php

namespace Mix\Grpc\Middleware;

use Mix\JsonRpc\Message\Request;
use Mix\JsonRpc\Message\Response;

/**
 * Interface MiddlewareInterface
 * @package Mix\JsonRpc\Middleware
 */
interface MiddlewareInterface
{

    /**
     * Process
     * @param Request[] $requests
     * @param RequestHandler $handler
     * @return Response[] $responses
     */
    public function process(array $requests, RequestHandler $handler): array;

}
