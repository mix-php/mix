<?php

namespace WebSocket\Handlers;

use Mix\Core\Middleware\MiddlewareHandler;
use Mix\Helper\JsonHelper;
use Mix\Http\Message\Request;
use Mix\WebSocket\Frame;
use Mix\WebSocket\Handler\WebSocketHandlerInterface;
use Mix\WebSocket\WebSocketConnection;

/**
 * Class WebSocketHandler
 * @package WebSocket\Handlers
 * @author LIUJIAN <coder.keda@gmail.com>
 */
class WebSocketHandler implements WebSocketHandlerInterface
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
        $response = new Frame\TextFrame([
            'data' => JsonHelper::encode([
                'error' => [
                    'code'    => -32600,
                    'message' => 'Invalid Request',
                ],
                'id'    => null,
            ], JSON_UNESCAPED_UNICODE),
        ]);
        if (!$frame->isTextFrame()) {
            app()->ws->push($response);
            return;
        }
        $data = json_decode($frame->data, true);
        if (!$data) {
            app()->ws->push($response);
            return;
        }
        if (!isset($data['method']) || !isset($data['params']) || !isset($data['id'])) {
            $response = new Frame\TextFrame([
                'data' => JsonHelper::encode([
                    'error' => [
                        'code'    => -32700,
                        'message' => 'Parse error',
                    ],
                    'id'    => null,
                ], JSON_UNESCAPED_UNICODE),
            ]);
            app()->ws->push($response);
            return;
        }
        // 路由到控制器
        list($controller, $action) = explode('.', $data['method']);
        $controller = \Mix\Helper\NameHelper::snakeToCamel($controller, true) . 'Controller';
        $controller = 'WebSocket\\Controllers\\' . $controller;
        $action     = 'action' . \Mix\Helper\NameHelper::snakeToCamel($action, true);
        $response   = new Frame\TextFrame([
            'data' => JsonHelper::encode([
                'error' => [
                    'code'    => -32601,
                    'message' => 'Method not found',
                ],
                'id'    => $data['id'],
            ], JSON_UNESCAPED_UNICODE),
        ]);
        if (!class_exists($controller)) {
            app()->ws->push($response);
            return;
        }
        $controller = new $controller;
        if (!method_exists($controller, $action)) {
            app()->ws->push($response);
            return;
        }
        // 通过中间件执行功能
        $middlewares = MiddlewareHandler::newInstances('WebSocket\\Middleware', ['Before', 'After']);
        $callback    = [$controller, $action];
        $params      = [$data['params'], $data['id']];
        MiddlewareHandler::run($callback, $params, $middlewares);
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
