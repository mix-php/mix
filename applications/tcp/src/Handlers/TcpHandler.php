<?php

namespace Tcp\Handlers;

use Mix\Tcp\Handler\HandlerInterface;
use Mix\Tcp\TcpConnection;

/**
 * Class TcpHandler
 * @package Tcp\Handlers
 * @author LIUJIAN <coder.keda@gmail.com>
 */
class TcpHandler implements HandlerInterface
{

    /**
     * 开启连接
     * @param TcpConnection $tcp
     */
    public function connect(TcpConnection $tcp)
    {
        // TODO: Implement open() method.
    }

    /**
     * 处理消息
     * @param TcpConnection $tcp
     * @param string $data
     */
    public function receive(TcpConnection $tcp, string $data)
    {
        // TODO: Implement message() method.
    }

    /**
     * 连接关闭
     * @param TcpConnection $tcp
     */
    public function close(TcpConnection $tcp)
    {
        // TODO: Implement close() method.
    }

}
