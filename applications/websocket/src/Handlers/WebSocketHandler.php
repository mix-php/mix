<?php

namespace WebSocket\Handlers;

use Mix\WebSocket\Connection;
use WebSocket\Exceptions\ExecutionException;
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
        $this->init();
    }

    /**
     * 初始化
     */
    public function init()
    {
        // 实例化控制器
        foreach ($this->methods as $method => $callback) {
            list($class, $action) = $callback;
            $this->methods[$method] = [new $class, $action];
        }
    }

    /**
     * Invoke
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
        if (!isset($data['method']) || (!isset($data['params']) or !is_array($data['params'])) || !isset($data['id'])) {
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
        try {
            $result = call_user_func($this->methods[$method], $params);
        } catch (ExecutionException $exception) {
            SendHelper::error($conn, $exception->getCode(), $exception->getMessage(), $id);
            return;
        }
        SendHelper::data($conn, $result, $id);
    }

}
