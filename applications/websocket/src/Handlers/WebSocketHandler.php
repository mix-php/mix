<?php

namespace WebSocket\Handlers;

use Mix\Concurrent\Coroutine\Channel;
use Mix\WebSocket\Connection;
use Swoole\WebSocket\Frame;
use WebSocket\Exceptions\ExecutionException;
use WebSocket\Helpers\SendHelper;
use WebSocket\Libraries\CloseWebSocketConnection;
use WebSocket\Libraries\SessionStorage;

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
     * @var Channel
     */
    public $sendChan;

    /**
     * @var SessionStorage
     */
    public $sessionStorage;

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
        $this->conn           = $conn;
        $this->sendChan       = new Channel(5);
        $this->sessionStorage = new SessionStorage();
        $this->init();
    }

    /**
     * Destruct
     */
    public function __destruct()
    {
        // TODO: Implement __destruct() method.
        $this->sendChan->close();
        $this->sessionStorage->clear();
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
        // 消息发送
        xgo(function () {
            while (true) {
                $data = $this->sendChan->pop();
                if (!$data) {
                    return;
                }
                if ($data instanceof CloseWebSocketConnection) {
                    $this->conn->close();
                    continue;
                }
                $frame       = new Frame();
                $frame->data = $data;
                $this->conn->send($frame);
            }
        });
        // 消息读取
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
            xgo([$this, 'runAction'], $data);
        }
    }

    /**
     * 执行功能
     * @param $data
     */
    public function runAction($data)
    {
        // 解析数据
        $data = json_decode($data, true);
        if (!$data) {
            SendHelper::error($this->sendChan, -32600, 'Invalid Request');
            return;
        }
        if (!isset($data['method']) || (!isset($data['params']) or !is_array($data['params'])) || !isset($data['id'])) {
            SendHelper::error($this->sendChan, -32700, 'Parse error');
            return;
        }
        // 定义变量
        $method = $data['method'];
        $params = $data['params'];
        $id     = $data['id'];
        // 路由到控制器
        if (!isset($this->methods[$method])) {
            SendHelper::error($this->sendChan, -32601, 'Method not found', $id);
            return;
        }
        // 执行
        try {
            $result = call_user_func($this->methods[$method], $this->sendChan, $this->sessionStorage, $params);
        } catch (ExecutionException $exception) {
            SendHelper::error($this->sendChan, $exception->getCode(), $exception->getMessage(), $id);
            return;
        }
        SendHelper::data($this->sendChan, $result, $id);
    }

}
