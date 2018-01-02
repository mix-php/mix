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
        $server->on('Request', [$this, 'onRequest']);
        $server->on('Open', [$this, 'onOpen']);
        $server->on('Message', [$this, 'onMessage']);
        $server->on('Close', [$this, 'onClose']);
        // 创建内存表
        $table = new \Swoole\Table(8192);
        $table->column('fd', \Swoole\Table::TYPE_INT);
        $table->create();
        // 添加属性至服务
        $server->setServerAttribute('table', $table);
        // 启动服务
        return $server->start();
    }

    // HTTP请求事件回调函数
    public function onRequest(\Swoole\WebSocket\Server $webSocket, \mix\swoole\Request $request, \mix\swoole\Response $response)
    {
        // 手动路由到控制器
        $pathInfo = $request->server('path_info');
        if ($pathInfo == '/send_all') {

        }
    }

    // 连接事件回调函数
    public function onOpen(\Swoole\WebSocket\Server $webSocket, $fd, \mix\swoole\Request $request)
    {
        // 效验token
        $tokenId = $request->get('access_token');
        \Mix::app()->webSocketToken->setTokenId($tokenId);
        $userinfo = \Mix::app()->webSocketToken->get('userinfo');
        if (empty($userinfo)) {
            echo "server: token error fd{$fd}\n";
            $webSocket->close($fd);
        }
        // 保存fd
        $webSocket->table->set($fd, ['fd' => $fd]);
        echo "server: handshake success with fd{$fd}\n";
    }

    // 接收消息事件回调函数
    public function onMessage(\Swoole\WebSocket\Server $webSocket, \Swoole\WebSocket\Frame $frame)
    {
        echo "receive from {$frame->fd}:{$frame->data},opcode:{$frame->opcode},fin:{$frame->finish}\n";
        $webSocket->push($frame->fd, "this is server");
    }

    // 关闭连接事件回调函数
    public function onClose(\Swoole\WebSocket\Server $webSocket, $fd)
    {
        // 删除fd
        $webSocket->table->del($fd);
        echo "client {$fd} closed\n";
    }

}
