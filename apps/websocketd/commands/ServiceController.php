<?php

namespace apps\websocketd\commands;

use mix\console\Controller;
use mix\swoole\Process;

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
     * @return \mix\swoole\WebSocketServer
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
        if ($pid = Process::getMasterPid(\Mix::app()->objects['webSocketServer']['setting']['pid_file'])) {
            return "mix-websocketd is running, PID : {$pid}." . PHP_EOL;
        }
        echo 'mix-websocketd start success.' . PHP_EOL;
        // 蜕变为守护进程
        if ($this->d) {
            Process::daemon();
        }
        // 创建服务
        $server = $this->getServer();
        $server->on('Open', [$this, 'onOpen']);
        $server->on('Message', [$this, 'onMessage']);
        $server->on('Close', [$this, 'onClose']);
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
         * token 方案，与上面的 session 方案，二选一使用即可

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
        $webSocket->fds[$fd]['session'] = [
            'uid'  => $userinfo['uid'],
            'name' => $userinfo['name'],
        ];

        // 异步订阅
        $redis = $this->getAsyncRedis();
        $redis->connect(function (\Swoole\Redis $client, $result) use ($userinfo) {
            // 错误处理
            if (!$result) {
                echo 'async redis error: [' . $client->errCode . '] ' . $client->errMsg . PHP_EOL;
                return;
            }
            // 订阅该用户id的消息队列
            $client->subscribe('emit_to_' . $userinfo['uid']);
        });
        $redis->on('Message', function (\Swoole\Redis $client, $result) use ($webSocket, $fd) {
            // 错误处理
            if (!$result) {
                echo 'async redis error: [' . $client->errCode . '] ' . $client->errMsg . PHP_EOL;
                return;
            }
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
        // 保存数据库连接
        $webSocket->fds[$fd]['redis'] = $redis;
    }

    // 接收消息事件回调函数
    public function onMessage(\Swoole\WebSocket\Server $webSocket, \Swoole\WebSocket\Frame $frame)
    {
        // 取出会话信息
        $userinfo = $webSocket->fds[$frame->fd]['session'];
        if (!$userinfo) {
            return;
        }
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
        if (isset($webSocket->fds[$fd]['session'])) {
            unset($webSocket->fds[$fd]['session']);
        }
        // 关闭数据库连接
        if (isset($webSocket->fds[$fd]['redis'])) {
            $webSocket->fds[$fd]['redis']->close();
            unset($webSocket->fds[$fd]['redis']);
        }
    }

}
