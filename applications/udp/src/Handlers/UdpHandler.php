<?php

namespace Udp\Handlers;

use Mix\Udp\ClientInfo;
use Mix\Udp\Handler\HandlerInterface;
use Mix\Udp\UdpSender;

/**
 * Class UdpHandler
 * @package Udp\Handlers
 * @author LIUJIAN <coder.keda@gmail.com>
 */
class UdpHandler implements HandlerInterface
{

    /**
     * 监听数据
     * @param UdpSender $udp
     * @param string $data
     * @param ClientInfo $clientInfo
     */
    public function packet(UdpSender $udp, string $data, ClientInfo $clientInfo)
    {
        // TODO: Implement packet() method.
        // 回复消息
        app()->udp->sendTo($clientInfo->ip, $clientInfo->port, "ok\n");
    }

}
