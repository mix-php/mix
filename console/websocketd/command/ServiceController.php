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
        $server = \Mix::app()->createObject('server');
        $server->on('Request', [$this, 'onRequest']);
        $server->on('Open', [$this, 'onOpen']);
        $server->on('Message', [$this, 'onMessage']);
        $server->on('Close', [$this, 'onClose']);
        // 创建内存表
        $table = new \swoole_table(1024);
        $table->column('fd', \swoole_table::TYPE_INT);
        $table->create();
        // 附加至服务上
        $server->attach('table', $table);
        // 启动服务
        return $server->start();
    }

    // HTTP请求事件回调函数
    public function onRequest($webSocket, \mix\swoole\WebSocketRequest $request, \mix\swoole\WebSocketResponse $response)
    {
        foreach ($webSocket->table as $fd => $item) {
            $webSocket->push($fd, 'message');
        }
        $response->setContent('dfd');
        $response->send();
    }

    // 连接事件回调函数
    public function onOpen($webSocket, $fd, \mix\swoole\WebSocketRequest $request)
    {
        $webSocket->table->set($fd, ['fd' => $fd]);
        echo "server: handshake success with fd{$fd}\n";
    }

    // 接收消息事件回调函数
    public function onMessage($webSocket, $frame)
    {
        echo "receive from {$frame->fd}:{$frame->data},opcode:{$frame->opcode},fin:{$frame->finish}\n";
        $webSocket->push($frame->fd, "this is server");
    }

    // 关闭连接事件回调函数
    public function onClose($webSocket, $fd)
    {
        $webSocket->table->del($fd);
        echo "client {$fd} closed\n";
    }

}
