<?php

namespace WebSocket\Controllers;

use Mix\Concurrent\Coroutine\Channel;
use Mix\Redis\Pool\ConnectionPool;
use WebSocket\Exceptions\ExecutionException;
use WebSocket\Forms\MessageForm;
use WebSocket\Helpers\JsonRpcHelper;
use WebSocket\Libraries\SessionStorage;

/**
 * Class MessageController
 * @package WebSocket\Controllers
 * @author liu,jian <coder.keda@gmail.com>
 */
class MessageController
{

    /**
     * 发送消息
     * @param Channel $sendChan
     * @param SessionStorage $sessionStorage
     * @param $params
     * @return array
     * @throws \PhpDocReader\AnnotationException
     * @throws \ReflectionException
     */
    public function emit(Channel $sendChan, SessionStorage $sessionStorage, $params)
    {
        // 使用模型
        $attributes = [
            'text' => array_shift($params),
        ];
        $model      = new MessageForm($attributes);
        $model->setScenario('emit');
        // 验证失败
        if (!$model->validate()) {
            throw new ExecutionException($model->getError(), 100001);
        }

        // 获取加入的房间id
        if (empty($sessionStorage->joinRoomId)) {
            // 给当前连接发送消息
            return [
                'message' => "You didn't join any room, please join a room first.",
            ];
        }

        // 给当前加入的房间发送消息
        xgo(function () use ($model, $sessionStorage) {
            $data = JsonRpcHelper::data([
                'message' => $model->text,
            ]);
            /** @var ConnectionPool $pool */
            $pool  = context()->get('redisPool');
            $redis = $pool->getConnection();
            $redis->publish("room_{$sessionStorage->joinRoomId}", $data);
            $redis->release();
        });

        // 给当前连接发送消息
        return [
            'status' => 'success',
        ];
    }

}
