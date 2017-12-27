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
        // 启动服务
        $server = \Mix::app()->createObject('server');
        $server->on('Open', [$this, 'onOpen']);
        $server->on('Message', [$this, 'onMessage']);
        $server->on('Close', [$this, 'onClose']);
        return $server->start();
    }

    // 连接事件回调函数
    public function onOpen($server, $request)
    {

        var_dump($request);

        echo "server: handshake success with fd{$request->fd}\n";
    }

    // 接收消息事件回调函数
    public function onMessage($server, $frame)
    {
        echo "receive from {$frame->fd}:{$frame->data},opcode:{$frame->opcode},fin:{$frame->finish}\n";
        $server->push($frame->fd, "this is server");
    }

    // 关闭连接事件回调函数
    public function onClose($server, $fd)
    {
        echo "client {$fd} closed\n";
    }

}
