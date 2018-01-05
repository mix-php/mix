<?php

namespace console\websocketd\controller;

/**
 * 广播控制器
 * @author 刘健 <coder.liu@qq.com>
 */
class broadcastController
{

    // 发出消息
    public function actionEmit(\Swoole\WebSocket\Server $webSocket)
    {
        // 给全部用户发送消息
        foreach ($webSocket->table as $fd => $item) {
            $webSocket->push($fd, '{"cmd":"broadcast","data":{"message":"hello"}}');
        }
        // 返回
        return ['errcode' => 0, 'errmsg' => 'ok'];
    }

}
