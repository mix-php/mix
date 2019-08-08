<?php

namespace WebSocket\Handlers;

use Mix\WebSocket\Connection;
use WebSocket\Helpers\SendHelper;

/**
 * Class WebSocketHandler
 * @package WebSocket\Handlers
 * @author liu,jian <coder.keda@gmail.com>
 */
class WebSocketHandler
{

    /**
     * @var Connection
     */
    public $conn;

    /**
     * @var callable[]
     */
    public $methods = [
        'hello.world' => [\Tcp\Controllers\HelloController::class, 'world'],
    ];

    /**
     * WebSocketHandler constructor.
     * @param Connection $connection
     */
    public function __construct(Connection $conn)
    {
        $this->conn = $conn;
    }

    /**
     * invoke
     */
    function __invoke()
    {
        while (true) {
            try {
                $data = $this->conn->recv();
            } catch (\Throwable $e) {
                // 忽略服务器主动关闭连接抛出的104错误
                if ($e->getCode() == 104) {
                    return;
                }
                throw $e;
            }
            xgo([$this, 'runAction'], $this->conn, $data);
        }
    }

    /**
     * 执行功能
     * @param Connection $conn
     * @param $data
     */
    public function runAction(Connection $conn, $data)
    {
        // 解析数据
        $data = json_decode($data, true);
        if (!$data) {
            SendHelper::error($conn, -32600, 'Invalid Request');
            return;
        }
        if (!isset($data['method']) || !isset($data['params']) || !isset($data['id'])) {
            SendHelper::error($conn, -32700, 'Parse error');
            return;
        }
        // 定义变量
        $method = $data['method'];
        $params = $data['params'];
        $id     = $data['id'];
        // 路由到控制器
        if (!isset($this->methods[$method])) {
            SendHelper::error($conn, -32601, 'Method not found', $id);
            return;
        }
        // 执行
        $result = call_user_func($this->methods[$method], $params);
        SendHelper::data($conn, $result, $id);
    }

}
