<?php

namespace WebSocket\Interceptors;

use Mix\Http\Message\Request;
use Mix\Http\Message\Response;
use Mix\WebSocket\Registry\InterceptorInterface;
use Mix\WebSocket\Registry\Support\HandshakeInterceptor;

/**
 * Class WebSocketInterceptor
 * @package WebSocket\Interceptors
 * @author LIUJIAN <coder.keda@gmail.com>
 */
class WebSocketInterceptor extends HandshakeInterceptor implements InterceptorInterface
{

    /**
     * 握手
     * @param Request $request
     * @param Response $response
     */
    public function handshake(Request $request, Response $response)
    {
        // TODO: Implement handshake() method.
        // 自定义握手处理

        // 默认握手处理
        parent::handshake($request, $response);
    }

}
