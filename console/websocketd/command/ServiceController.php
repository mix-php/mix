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
        $table = new \Swoole\Table(65536);
        $table->column('uid', \Swoole\Table::TYPE_INT);
        $table->column('name', \Swoole\Table::TYPE_STRING, 20);
        $table->create();
        // 添加属性至服务
        $server->setServerAttribute('table', $table);
        // 启动服务
        return $server->start();
    }

    // 连接事件回调函数
    public function onOpen(\Swoole\WebSocket\Server $webSocket, $fd, \mix\swoole\Request $request)
    {
        echo "onOpen {$fd}\n";


        // 效验session
        \Mix::app('webSocket')->sessionReader->loadSessionId($request);
        $userinfo = \Mix::app('webSocket')->sessionReader->get('userinfo');
        if (empty($userinfo)) {
            // 鉴权失败处理
            $webSocket->push($fd, json_encode(['error_code' => 300001]));
            $webSocket->close($fd);
            return;
        }

        /*
         * 与上面的 session 方案，二选一使用即可

        // 效验token
        \Mix::app('webSocket')->tokenReader->loadTokenId($request);
        $userinfo = \Mix::app('webSocket')->tokenReader->get('userinfo');
        if (empty($userinfo)) {
            // 鉴权失败处理
            $webSocket->push($fd, json_encode(['error_code' => 300001]));
            $webSocket->close($fd);
            return;
        }

        */

        // 保存会话信息
        $webSocket->table->set($fd, [
            'uid'  => $userinfo['uid'],
            'name' => $userinfo['name'],
        ]);

        echo "onOpen ok {$fd}\n";
    }

    // 接收消息事件回调函数
    public function onMessage(\Swoole\WebSocket\Server $webSocket, \Swoole\WebSocket\Frame $frame)
    {
        echo "onMessage {$frame->fd}, {$frame->data}\n";

        // 取出会话信息
        $userinfo = $webSocket->table->get($frame->fd);
        // 解析数据
        $data = json_decode($frame->data);
        if (!isset($data->cmd) || !isset($data->data)) {
            return;
        }
        $action = $data->cmd;
        // 执行功能
        \Mix::app('webSocket')->messageHandler
            ->setServer($webSocket)
            ->setFd($frame->fd)
            ->runAction($action, [$data->data, $userinfo]);
    }

    // 关闭连接事件回调函数
    public function onClose(\Swoole\WebSocket\Server $webSocket, $fd)
    {
        // 删除会话信息
        $webSocket->table->del($fd);
    }

}
