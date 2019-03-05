<?php

namespace WebSocket\Interceptors;

use Mix\Http\Message\Request;
use Mix\Http\Message\Response;
use Mix\WebSocket\Registry\InterceptorInterface;

class WebSocketInterceptor implements InterceptorInterface
{

    /**
     * 握手
     * @param Request $request
     * @param Response $response
     */
    public function handshake(Request $request, Response $response)
    {
        // TODO: Implement handshake() method.
    }

}
