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
     * @param \Swoole\Http2\Request $parameters
     * @param RequestHandler $handler
     * @return \Swoole\Http2\Response
     */
    public function process(\Swoole\Http2\Request $request, RequestHandler $handler): \Swoole\Http2\Response;

}
