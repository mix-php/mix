<?php

namespace WebSocket\Handlers;

use Mix\WebSocket\Message\Frame;
use Mix\WebSocket\Registry\HandlerInterface;

/**
 * Class WebSocketHandler
 * @package WebSocket\Handlers
 * @author LIUJIAN <coder.keda@gmail.com>
 */
class WebSocketHandler implements HandlerInterface
{

    /**
     * 处理消息
     * @return void
     */
    public function open()
    {
        // TODO: Implement open() method.
        var_dump('open');
    }

    /**
     * 处理消息
     * @return void
     */
    public function message(\Swoole\WebSocket\Frame $frame)
    {
        // TODO: Implement message() method.
        var_dump('message');
        var_dump($frame);
        $f = new Frame([
            'data' => 'dfsdfsdfsdf',
        ]);
        app()->ws->push($f);
        app()->ws->push($f);
    }

    /**
     * 连接关闭
     * @return void
     */
    public function close()
    {
        // TODO: Implement connectionClosed() method.
        var_dump('close');
    }

}
