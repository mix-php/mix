<?php

namespace WebSocketd\Commands\Service;

use Mix\Console\CommandLine\Flag;
use Mix\Helpers\ProcessHelper;

/**
 * Start 子命令
 * @author LIUJIAN <coder.keda@gmail.com>
 */
class StartCommand extends BaseCommand
{

    // 主函数
    public function main()
    {
        // 获取参数
        $daemon = Flag::bool(['d', 'daemon'], false);
        // 获取服务信息
        $server  = \Mix\WebSocket\Server::newInstance();
        $pidFile = $server->settings['pid_file'];
        $pid     = $this->getServicePid($pidFile);
        if ($pid) {
            println(sprintf(self::IS_RUNNING, $pid));
            return;
        }
        // 启动服务
        println(self::START_WELCOME);
        if ($daemon) {
            ProcessHelper::daemon();
        }
        $server->on('Open', [$this, 'onOpen']);
        $server->on('Message', [$this, 'onMessage']);
        $server->on('Close', [$this, 'onClose']);
        $server->start();
    }

    // 连接事件回调函数
    public function onOpen(\Swoole\WebSocket\Server $webSocket, $fd, \Mix\Http\Request $request)
    {
        // 效验session
        $userinfo = app()->sessionReader->loadSessionId($request)->get('userinfo');
        app()->sessionReader->close();
        if (empty($userinfo)) {
            // 鉴权失败处理
            $webSocket->close($fd);
            return;
        }

        /*
         * token 方案，与上面的 session 方案，二选一使用即可

        // 效验token
        $userinfo = app()->tokenReader->loadTokenId($request)->get('userinfo');
        app()->tokenReader->close();
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
        $redis = \Mix\Redis\Async\RedisConnection::newInstance();
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
                app()->error->handleException($e);
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
                app()->error->handleException($e);
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
        app()->messageHandler
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
