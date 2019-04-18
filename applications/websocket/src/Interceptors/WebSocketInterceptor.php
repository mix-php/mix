<?php

namespace WebSocket\Interceptors;

use Mix\Http\Message\Request\HttpRequest;
use Mix\Http\Message\Response\HttpResponse;
use Mix\WebSocket\Interceptor\WebSocketInterceptorInterface;
use Mix\WebSocket\Support\WebSocketHandshakeInterceptor;
use Mix\WebSocket\WebSocketConnection;

/**
 * Class WebSocketInterceptor
 * @package WebSocket\Interceptors
 * @author liu,jian <coder.keda@gmail.com>
 */
class WebSocketInterceptor extends WebSocketHandshakeInterceptor implements WebSocketInterceptorInterface
{

    /**
     * 握手
     * @param WebSocketConnection $ws
     * @param HttpRequest $request
     * @param HttpResponse $response
     */
    public function handshake(WebSocketConnection $ws, HttpRequest $request, HttpResponse $response)
    {
        // TODO: Implement handshake() method.
        // 自定义握手处理
        $uid  = app()->session->get('uid');
        $name = app()->session->get('name');
        if (empty($uid) || empty($name)) {
            $response->statusCode = 400; // 根据RFC, 握手失败状态码为400
            $response->send();
            $ws->disconnect();
            return;
        }

        // 使用tcpSession保存会话信息
        app()->tcpSession->set('uid', $uid);
        app()->tcpSession->set('name', $name);

        // 默认握手处理
        parent::handshake($ws, $request, $response);
    }

}
