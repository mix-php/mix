<?php

namespace WebSocket\Handlers;

use Mix\Http\Message\Request;
use Mix\WebSocket\Frame;
use Mix\WebSocket\Registry\HandlerInterface;
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
    }

    /**
     * 关闭连接
     * @param WebSocketConnection $ws
     */
    public function close(WebSocketConnection $ws)
    {
        // TODO: Implement close() method.
    }

}
