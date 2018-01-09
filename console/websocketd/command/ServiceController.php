<?php

namespace console\websocketd\command;

use mix\console\Controller;

/**
 * 服务控制器
 * @author 刘健 <coder.liu@qq.com>
 */
class ServiceController extends Controller
{

    // 是否后台运行
    protected $d = false;

    // 启动服务
    public function actionStart()
    {
        // 蜕变为守护进程
        if ($this->d) {
            self::daemon();
        }
        // 创建服务
        $server = \Mix::app()->createObject('webSocketServer');
        $server->on('Open', [$this, 'onOpen']);
        $server->on('Message', [$this, 'onMessage']);
        $server->on('Close', [$this, 'onClose']);
        // 创建内存表
        $table = new \Swoole\Table(8192);
        $table->column('room_id', \Swoole\Table::TYPE_INT);
        $table->column('uid', \Swoole\Table::TYPE_INT);
        $table->column('name', \Swoole\Table::TYPE_STRING, 20);
        $table->create();
        // 添加属性至服务
        $server->setServerAttribute('table', $table);
        // 启动服务
        return $server->start();
    }

    // 连接事件回调函数
    public function onOpen(\Swoole\WebSocket\Server $webSocket, $fd)
    {
        // 效验session
        \Mix::app('webSocket')->sessionReader->loadSessionId();
        $userinfo = \Mix::app('webSocket')->sessionReader->get('userinfo');
        if (empty($userinfo)) {
            $webSocket->push($fd, '{"cmd":"permission_denied"}');
            $webSocket->close($fd);
            return;
        }

        /*
         * 与上面的 session 方案，二选一使用即可

        // 效验token
        \Mix::app('webSocket')->tokenReader->loadTokenId();
        $userinfo = \Mix::app('webSocket')->tokenReader->get('userinfo');
        if (empty($userinfo)) {
            echo "server: access_token error fd{$fd}\n";
            $webSocket->push($fd, '{"cmd":"permission_denied"}');
            $webSocket->close($fd);
            return;
        }

        */

        // 获取房间id
        $roomId = (int)\Mix::app('webSocket')->request->get('room_id');
        // 保存用户信息，使用fd做索引
        $webSocket->table->set($fd, [
            'room_id' => $roomId,
            'uid'     => $userinfo['uid'],
            'name'    => $userinfo['name'],
        ]);
        // 发送加入广播
        foreach ($webSocket->table as $key => $item) {
            if ($item['room_id'] == $roomId) {
                $webSocket->push($key, '{"cmd":"broadcast","data":{"message":"[' . $userinfo['name'] . '] 加入房间"}}');
            }
        }
    }

    // 接收消息事件回调函数
    public function onMessage(\Swoole\WebSocket\Server $webSocket, \Swoole\WebSocket\Frame $frame)
    {
        echo "receive from {$frame->fd}:{$frame->data},opcode:{$frame->opcode},fin:{$frame->finish}\n";
        $webSocket->push($frame->fd, '{"cmd":"message","data":{"message":"hello"}}');
    }

    // 关闭连接事件回调函数
    public function onClose(\Swoole\WebSocket\Server $webSocket, $fd)
    {
        // 通过索引fd找到用户信息
        $userinfo = [];
        foreach ($webSocket->table as $key => $item) {
            if ($key == $fd) {
                $userinfo = $item;
                break;
            }
        }
        if (empty($userinfo)) {
            return;
        }
        // 发送退出广播
        foreach ($webSocket->table as $key => $item) {
            if ($item['room_id'] == $userinfo['room_id']) {
                $webSocket->push($key, '{"cmd":"broadcast","data":{"message":"[' . $userinfo['name'] . '] 退出房间"}}');
            }
        }
        // 删除fd
        $webSocket->table->del($fd);
    }

}
