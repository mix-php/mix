<?php

namespace console\websocketd\controller;

/**
 * 广播控制器
 * @author 刘健 <coder.liu@qq.com>
 */
class broadcastController
{

    // 发送消息
    public function actionSend(\Swoole\WebSocket\Server $webSocket)
    {
        // 给全部用户发送消息
        foreach ($webSocket->table as $fd => $item) {
            $webSocket->push($fd, 'message');
        }
        // 返回
        return ['errcode' => 0, 'errmsg' => 'ok'];
    }

}
