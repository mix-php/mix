<?php

namespace WebSocket\Handlers;

use Mix\Http\Message\Request;
use Mix\WebSocket\Frame;
use Mix\WebSocket\Registry\HandlerInterface;

/**
 * Class WebSocketHandler
 * @package WebSocket\Handlers
 * @author LIUJIAN <coder.keda@gmail.com>
 */
class WebSocketHandler implements HandlerInterface
{

    /**
     * 开启连接
     * @param Request $request
     */
    public function open(Request $request)
    {
        // TODO: Implement open() method.
    }

    /**
     * 消息处理
     * @param Frame $frame
     */
    public function message(Frame $frame)
    {
        // TODO: Implement message() method.
    }

    /**
     * 关闭连接
     */
    public function close()
    {
        // TODO: Implement close() method.
    }

}
