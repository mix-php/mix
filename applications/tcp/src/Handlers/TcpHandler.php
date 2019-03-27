<?php

namespace Tcp\Handlers;

use Mix\Helper\JsonHelper;
use Mix\Tcp\Handler\TcpHandlerInterface;
use Mix\Tcp\TcpConnection;

/**
 * Class TcpHandler
 * @package Tcp\Handlers
 * @author liu,jian <coder.keda@gmail.com>
 */
class TcpHandler implements TcpHandlerInterface
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
        // 主动退出处理
        if (base64_encode($data) == '//T//QYNCg==') {
            $tcp->disconnect();
        }
        // 解析数据
        $data = json_decode($data, true);
        if (!$data) {
            $response = [
                'jsonrpc' => '2.0',
                'error'   => [
                    'code'    => -32600,
                    'message' => 'Invalid Request',
                ],
                'id'      => null,
            ];
            $tcp->send(JsonHelper::encode($response) . "\n");
            return;
        }
        if (!isset($data['method']) || !isset($data['params']) || !isset($data['id'])) {
            $response = [
                'jsonrpc' => '2.0',
                'error'   => [
                    'code'    => -32700,
                    'message' => 'Parse error',
                ],
                'id'      => null,
            ];
            $tcp->send(JsonHelper::encode($response) . "\n");
            return;
        }
        // 路由到控制器
        list($controller, $action) = explode('.', $data['method']);
        $controller = \Mix\Helper\NameHelper::snakeToCamel($controller, true) . 'Controller';
        $controller = 'Tcp\\Controllers\\' . $controller;
        $action     = 'action' . \Mix\Helper\NameHelper::snakeToCamel($action, true);
        $response   = [
            'jsonrpc' => '2.0',
            'error'   => [
                'code'    => -32601,
                'message' => 'Method not found',
            ],
            'id'      => $data['id'],
        ];
        if (!class_exists($controller)) {
            $tcp->send(JsonHelper::encode($response) . "\n");
            return;
        }
        $controller = new $controller;
        if (!method_exists($controller, $action)) {
            $tcp->send(JsonHelper::encode($response) . "\n");
            return;
        }
        call_user_func([$controller, $action], $data['params'], $data['id']);
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
