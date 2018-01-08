<?php

namespace console\websocketd\request;

/**
 * 广播控制器
 * @author 刘健 <coder.liu@qq.com>
 */
class BroadcastController
{

    // 发送
    protected static function emit(\Swoole\WebSocket\Server $webSocket, $message)
    {
        // 给全部用户发送消息
        foreach ($webSocket->table as $fd => $item) {
            $webSocket->push($fd, '{"cmd":"broadcast","data":{"message":"' . $message . '"}}');
        }
    }

    // 广播
    public function actionEmit($webSocket)
    {
        // 给全部用户发送消息
        foreach ($webSocket->table as $fd => $item) {
            $webSocket->push($fd, '{"cmd":"broadcast","data":{"message":"hello"}}');
        }
        // 返回
        return ['errcode' => 0, 'errmsg' => 'ok'];
    }

    // 退出房间
    public static function exitRoom(\Swoole\WebSocket\Server $webSocket)
    {
        self::emit($webSocket, 'exit room');
    }

}
