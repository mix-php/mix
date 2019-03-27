<?php

namespace Udp\Handlers;

use Mix\Udp\Handler\UdpHandlerInterface;
use Mix\Udp\UdpSender;

/**
 * Class UdpHandler
 * @package Udp\Handlers
 * @author liu,jian <coder.keda@gmail.com>
 */
class UdpHandler implements UdpHandlerInterface
{

    /**
     * 监听数据
     * @param UdpSender $udp
     * @param string $data
     * @param array $clientInfo
     */
    public function packet(UdpSender $udp, string $data, array $clientInfo)
    {
        // TODO: Implement packet() method.
        // 回复消息
        app()->udp->sendTo($clientInfo['address'], $clientInfo['port'], "Receive successful!\n");
    }

}
