<?php

namespace WebSocket\Interceptors;

use Mix\Http\Message\Request;
use Mix\Http\Message\Response;
use Mix\WebSocket\Interceptor\InterceptorInterface;
use Mix\WebSocket\Support\HandshakeInterceptor;

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
        $userinfo = app()->session->get('userinfo');
        if (empty($userinfo['uid']) || empty($userinfo['name'])) {
            $response->statusCode = 500;
            $response->send();
            return;
        }

        // 使用tcpSession保存会话信息
        app()->tcpSession->set('uid', $userinfo['uid']);
        app()->tcpSession->set('name', $userinfo['name']);

        // 默认握手处理
        parent::handshake($request, $response);
    }

}
