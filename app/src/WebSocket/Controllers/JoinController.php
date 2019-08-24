<?php

namespace App\WebSocket\Controllers;

use Mix\Concurrent\Coroutine\Channel;
use Mix\Redis\Coroutine\RedisConnection;
use Mix\Redis\Pool\ConnectionPool;
use Swoole\WebSocket\Frame;
use App\WebSocket\Exceptions\ExecutionException;
use App\WebSocket\Helpers\JsonRpcHelper;
use App\WebSocket\Libraries\CloseConnection;
use App\WebSocket\Forms\JoinForm;
use App\WebSocket\Libraries\SessionStorage;

/**
 * Class JoinController
 * @package App\WebSocket\Controllers
 * @author liu,jian <coder.keda@gmail.com>
 */
class JoinController
{

    /**
     * 加入房间
     * @param Channel $sendChan
     * @param SessionStorage $sessionStorage
     * @param $params
     * @return array
     * @throws \PhpDocReader\AnnotationException
     * @throws \ReflectionException
     */
    public function room(Channel $sendChan, SessionStorage $sessionStorage, $params)
    {
        // 验证数据
        $attributes = [
            'roomId' => (string)array_shift($params),
            'name'   => (string)array_shift($params),
        ];
        $model      = new JoinForm($attributes);
        $model->setScenario('room');
        if (!$model->validate()) {
            throw new ExecutionException($model->getError(), 100001);
        }

        // 保存当前加入的房间
        $sessionStorage->joinRoomId = $model->roomId;

        // 重复加入处理
        $redis = $sessionStorage->redis;
        if ($redis) {
            $redis->disabled = true; // 标记废除
            $redis->disconnect(); // 关闭后会导致 subscribe 的连接抛出错误
        }

        // 订阅房间的频道
        xgo(function () use ($sendChan, $sessionStorage, $model) {
            // 订阅房间的频道
            $redis = $sessionStorage->redis = context()->get(RedisConnection::class);
            try {
                $redis->subscribe(["room_{$model->roomId}"], function ($instance, $channel, $message) use ($sendChan) {
                    $frame       = new Frame();
                    $frame->data = $message;
                    $sendChan->push($frame);
                });
            } catch (\Throwable $e) {
                // redis连接异常断开处理
                if (!empty($redis->disabled)) {
                    return;
                }
                $sendChan->push(new CloseConnection());
            }
        });

        // 给其他订阅当前房间的连接发送加入消息
        xgo(function () use ($model) {
            $data = JsonRpcHelper::notification('message.update', [
                'text' => "'{$model->name}' joined the room, room_id: {$model->roomId}.",
            ]);
            /** @var ConnectionPool $pool */
            $pool  = context()->get('redisPool');
            $redis = $pool->getConnection();
            $redis->publish("room_{$model->roomId}", $data);
            $redis->release();
        });

        // 给当前连接发送加入消息
        return [
            'message' => "I joined the room, room_id: {$model->roomId}.",
        ];
    }

}
