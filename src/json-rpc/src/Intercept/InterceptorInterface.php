<?php

namespace Mix\JsonRpc\Intercept;

use Mix\JsonRpc\Message\Request;
use Mix\JsonRpc\Message\Response;

/**
 * Interface InterceptorInterface
 * @package Mix\JsonRpc\Intercept
 */
interface InterceptorInterface
{

    /**
     * Process
     * @param Request $request
     * @param Response $response
     * @param RequestHandler $handler
     * @return Response
     */
    public function process(Request $request, Response $response, RequestHandler $handler): Response;

}
