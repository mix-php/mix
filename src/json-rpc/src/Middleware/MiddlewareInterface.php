<?php

namespace Mix\JsonRpc\Middleware;

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
     * @param Request $request
     * @param RequestHandler $handler
     * @return Response
     */
    public function process(Request $request, RequestHandler $handler): Response;

}
