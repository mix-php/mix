<?php

namespace WebSocket\Handlers;

use Mix\Http\Message\Request;
use Mix\WebSocket\Frame;
use Mix\WebSocket\Handler\HandlerInterface;
use Mix\WebSocket\WebSocketConnection;

/**
 * Class WebSocketHandler
 * @package WebSocket\Handlers
 * @author LIUJIAN <coder.keda@gmail.com>
 */
class WebSocketHandler implements HandlerInterface
{

    /**
     * 开启连接
     * @param WebSocketConnection $ws
     * @param Request $request
     */
    public function open(WebSocketConnection $ws, Request $request)
    {
        // TODO: Implement open() method.
    }

    /**
     * 消息处理
     * @param WebSocketConnection $ws
     * @param Frame $frame
     */
    public function message(WebSocketConnection $ws, Frame $frame)
    {
        // TODO: Implement message() method.
        // 解析数据
        if (!$frame->isTextFrame()) {
            return;
        }
        $data = json_decode($frame->data, true);
        if (!isset($data['method']) || !isset($data['params'])) {
            return;
        }
        // 路由到控制器
        list($controller, $action) = explode('.', $data['method']);
        $controller = \Mix\Helper\NameHelper::snakeToCamel($controller, true) . 'Controller';
        $controller = 'WebSocket\\Controllers\\' . $controller;
        $action     = 'action' . \Mix\Helper\NameHelper::snakeToCamel($action, true);
        if (!class_exists($controller)) {
            return;
        }
        $controller = new $controller;
        if (!method_exists($controller, $action)) {
            return;
        }
        call_user_func([$controller, $action], $data['params']);
    }

    /**
     * 关闭连接
     * @param WebSocketConnection $ws
     */
    public function close(WebSocketConnection $ws)
    {
        // TODO: Implement close() method.
        // 清除会话信息
        app()->tcpSession->clear();
    }

}
