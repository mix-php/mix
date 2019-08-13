<?php

namespace WebSocket\Controllers;

use Mix\Concurrent\Coroutine\Channel;
use Mix\Helper\JsonHelper;
use Mix\Redis\Coroutine\RedisConnection;
use Mix\Redis\Pool\ConnectionPool;
use Swoole\WebSocket\Frame;
use WebSocket\Exceptions\ExecutionException;
use WebSocket\Libraries\CloseWebSocketConnection;
use WebSocket\Forms\JoinForm;

/**
 * Class JoinController
 * @package WebSocket\Controllers
 * @author liu,jian <coder.keda@gmail.com>
 */
class JoinController
{

    /**
     * @var string
     */
    public $joinRoomId;

    /**
     * @var RedisConnection
     */
    public $redis;

    /**
     * Destruct
     */
    public function __destruct()
    {
        // TODO: Implement __destruct() method.
        $this->closeSubscribeRedis();
    }

    /**
     * 加入房间
     * @param Channel $sendChan
     * @param $params
     * @return array
     */
    public function room(Channel $sendChan, $params)
    {
        // 验证数据
        $attributes = [
            'room_id' => array_shift($params),
        ];
        $model      = new JoinForm($attributes);
        $model->setScenario('room');
        if (!$model->validate()) {
            throw new ExecutionException($model->getError(), 100001);
        }

        // 保存当前加入的房间
        $this->joinRoomId = $model->roomId;

        // 重复加入处理
        $this->closeSubscribeRedis();

        // 订阅房间的频道
        xgo(function () use ($sendChan, $model) {
            // 订阅房间的频道
            $redis = $this->redis = context()->get(RedisConnection::class);
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
                $sendChan->push(new CloseWebSocketConnection());
            }
        });

        // 给其他订阅当前房间的连接发送加入消息
        $message = JsonHelper::encode([
            'method' => 'room.message',
            'result' => [
                'message' => "{$model->name} joined the room, id: {$model->roomId}.",
            ],
            'id'     => null,
        ], JSON_UNESCAPED_UNICODE);
        /** @var ConnectionPool $pool */
        $pool  = context()->get('redisPool');
        $redis = $pool->getConnection();
        $redis->publish("room_{$model->roomId}", $message);
        $redis->release();

        // 给当前连接发送加入消息
        return [
            'message' => "i joined the room, id: {$model->roomId}.",
        ];
    }

    /**
     * 关闭订阅的redis
     */
    protected function closeSubscribeRedis()
    {
        if (!$this->redis) {
            return;
        }
        $this->redis->disabled = true; // 标记废除
        $this->redis->disconnect(); // 关闭后会导致 subscribe 的连接抛出错误
    }

}
