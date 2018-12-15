<?php

namespace WebSocketd\Commands;

use Mix\Console\Command;
use Mix\Facades\Error;
use Mix\Facades\Output;
use Mix\Helpers\ProcessHelper;

/**
 * Service 命令
 * @author 刘健 <coder.liu@qq.com>
 */
class ServiceCommand extends Command
{

    // 是否后台运行
    public $daemon = false;

    // 选项配置
    public function options()
    {
        return ['daemon'];
    }

    // 选项别名配置
    public function optionAliases()
    {
        return ['d' => 'daemon'];
    }

    // 启动服务
    public function actionStart()
    {
        $server = \Mix\WebSocket\Server::newInstance();
        $pid    = ProcessHelper::readPidFile($server->settings['pid_file']);
        // 重复启动处理
        if ($pid) {
            println("mix-websocketd is running, PID : {$pid}.");
            return;
        }
        // 启动提示
        println('mix-websocketd start successed.');
        // 蜕变为守护进程
        if ($this->daemon) {
            ProcessHelper::daemon();
        }
        // 启动服务
        $server->on('Open', [$this, 'onOpen']);
        $server->on('Message', [$this, 'onMessage']);
        $server->on('Close', [$this, 'onClose']);
        $server->start();
    }

    // 停止服务
    public function actionStop()
    {
        $server = \Mix\WebSocket\Server::newInstance();
        $pid    = ProcessHelper::readPidFile($server->settings['pid_file']);
        if ($pid) {
            ProcessHelper::kill($pid);
            while (ProcessHelper::isRunning($pid)) {
                // 等待进程退出
                usleep(100000);
            }
            println('mix-websocketd stop completed.');
        } else {
            println('mix-websocketd is not running.');
        }
    }

    // 重启服务
    public function actionRestart()
    {
        $this->actionStop();
        $this->actionStart();
        // 返回退出码
        return;
    }

    // 查看服务状态
    public function actionStatus()
    {
        $server = \Mix\WebSocket\Server::newInstance();
        $pid    = ProcessHelper::readPidFile($server->settings['pid_file']);
        if ($pid) {
            println("mix-websocketd is running, PID : {$pid}.");
        } else {
            println('mix-websocketd is not running.');
        }
    }

    // 连接事件回调函数
    public function onOpen(\Swoole\WebSocket\Server $webSocket, $fd, \Mix\Http\Request $request)
    {
        // 效验session
        $userinfo = app('websocket')->sessionReader->loadSessionId($request)->get('userinfo');
        app('websocket')->sessionReader->close();
        if (empty($userinfo)) {
            // 鉴权失败处理
            $webSocket->close($fd);
            return;
        }

        /*
         * token 方案，与上面的 session 方案，二选一使用即可

        // 效验token
        $userinfo = app('websocket')->tokenReader->loadTokenId($request)->get('userinfo');
        app('websocket')->tokenReader->close();
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
        $redis = \Mix\Redis\Async\RedisConnection::newInstanceByName('libraries.[async.redis]');
        $redis->on('Message', function (\Swoole\Redis $client, $result) use ($webSocket, $fd) {
            try {
                // 错误处理
                if (!$result) {
                    $message = 'async redis error: [' . $client->errCode . '] ' . $client->errMsg . PHP_EOL;
                    throw new \Exception($message);
                }
                // 将消息队列的消息发送至客户端
                list($type, , $message) = $result;
                if ($type == 'message') {
                    $webSocket->push($fd, $message);
                }
            } catch (\Throwable $e) {
                // 处理异常
                Error::handleException($e);
            }
        });
        $redis->on('Close', function (\Swoole\Redis $client) use ($webSocket, $fd) {
            // 关闭 WS 连接
            $webSocket->close($fd);
        });
        $redis->connect(function (\Swoole\Redis $client, $result) use ($userinfo, $webSocket, $fd) {
            try {
                // 错误处理
                if (!$result) {
                    // 抛出错误
                    $message = 'async redis error: [' . $client->errCode . '] ' . $client->errMsg . PHP_EOL;
                    throw new \Exception($message);
                }
                // 订阅该用户id的消息队列
                $channels[] = 'emit_to_' . $userinfo['uid'];
                call_user_func_array([$client, 'subscribe'], $channels);
            } catch (\Throwable $e) {
                // 处理异常
                Error::handleException($e);
                // 关闭 WS 连接
                $webSocket->close($fd);
            }
        });
        // 保存数据库连接
        $webSocket->fds[$fd]['redis'] = $redis;
    }

    // 接收消息事件回调函数
    public function onMessage(\Swoole\WebSocket\Server $webSocket, \Swoole\WebSocket\Frame $frame)
    {
        // 取出会话信息
        if (!isset($webSocket->fds[$frame->fd]['session'])) {
            return;
        }
        $userinfo = $webSocket->fds[$frame->fd]['session'];
        // 解析数据
        $data = json_decode($frame->data, true);
        if (!isset($data['event']) || !isset($data['params'])) {
            return;
        }
        $event = $data['event'];
        // 执行功能
        app('websocket')->messageHandler
            ->setServer($webSocket)
            ->setFd($frame->fd)
            ->runAction($event, [$data['params'], $userinfo]);
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
        // 删除整个 fd 的数据
        unset($webSocket->fds[$fd]);
    }

}
