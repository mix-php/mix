<?php

namespace apps\websocketd\commands;

use mix\console\Controller;

/**
 * 服务控制器
 * @author 刘健 <coder.liu@qq.com>
 */
class ServiceController extends Controller
{

    // 是否后台运行
    protected $d = false;

    /**
     * 获取服务
     * @return \mix\server\WebSocketServer
     */
    public function getServer()
    {
        return \Mix::app()->createObject('webSocketServer');
    }

    /**
     * 获取异步redis
     * @return \mix\async\Redis
     */
    public function getAsyncRedis()
    {
        return \Mix::app()->createObject('asyncRedis');
    }

    // 启动服务
    public function actionStart()
    {
        // 蜕变为守护进程
        if ($this->d) {
            self::daemon();
        }
        // 创建服务
        $server = $this->getServer();
        $server->on('Open', [$this, 'onOpen']);
        $server->on('Message', [$this, 'onMessage']);
        $server->on('Close', [$this, 'onClose']);
        // 创建内存表
        $table = new \Swoole\Table(65536);
        $table->column('uid', \Swoole\Table::TYPE_INT);
        $table->column('name', \Swoole\Table::TYPE_STRING, 20);
        $table->create();
        // 添加至服务属性
        $server->table = $table;
        // 启动服务
        $server->start();
    }

    // 连接事件回调函数
    public function onOpen(\Swoole\WebSocket\Server $webSocket, $fd, \mix\swoole\Request $request)
    {
        // 效验session
        $userinfo = \Mix::app('webSocket')->sessionReader->loadSessionId($request)->get('userinfo');
        \Mix::app('webSocket')->sessionReader->close();
        if (empty($userinfo)) {
            // 鉴权失败处理
            $webSocket->close($fd);
            return;
        }

        /*
         * 与上面的 session 方案，二选一使用即可

        // 效验token
        $userinfo = \Mix::app('webSocket')->tokenReader->loadTokenId($request)->get('userinfo');
        \Mix::app('webSocket')->tokenReader->close();
        if (empty($userinfo)) {
            // 鉴权失败处理
            $webSocket->close($fd);
            return;
        }

        */

        // 保存会话信息
        $webSocket->table->set($fd, [
            'uid'  => $userinfo['uid'],
            'name' => $userinfo['name'],
        ]);
        // 异步订阅
        $redis = $this->getAsyncRedis();
        $redis->on('Message', function (\Swoole\Redis $client, $result) use ($webSocket, $fd) {
            // 将消息队列的消息发送至客户端
            list($type, , $message) = $result;
            if ($type == 'message') {
                $webSocket->push($fd, $message);
            }
        });
        $redis->on('Close', function (\Swoole\Redis $client) use ($webSocket, $fd) {
            // 关闭WS连接
            $webSocket->close($fd);
        });
        $redis->connect(function (\Swoole\Redis $client, $result) use ($userinfo) {
            // 订阅该用户id的消息队列
            $client->subscribe('emit_to_' . $userinfo['uid']);
        });
        // 保存连接
        $webSocket->redisConnections[$fd] = $redis;
    }

    // 接收消息事件回调函数
    public function onMessage(\Swoole\WebSocket\Server $webSocket, \Swoole\WebSocket\Frame $frame)
    {
        // 取出会话信息
        $userinfo = $webSocket->table->get($frame->fd);
        // 解析数据
        $data = json_decode($frame->data, true);
        if (!isset($data['cmd']) || !isset($data['data'])) {
            return;
        }
        $action = $data['cmd'];
        // 执行功能
        \Mix::app('webSocket')->messageHandler
            ->setServer($webSocket)
            ->setFd($frame->fd)
            ->runAction($action, [$data['data'], $userinfo]);
    }

    // 关闭连接事件回调函数
    public function onClose(\Swoole\WebSocket\Server $webSocket, $fd)
    {
        // 删除会话信息
        $webSocket->table->del($fd);
        // 删除redis连接
        if (isset($webSocket->redisConnections[$fd])) {
            $webSocket->redisConnections[$fd]->close();
            unset($webSocket->redisConnections[$fd]);
        }
    }

}
