<?php

namespace WebSocket\Controllers;

use Mix\WebSocket\Frame\TextFrame;
use WebSocket\Models\JoinForm;

/**
 * Class JoinController
 * @package WebSocket\Controllers
 * @author LIUJIAN <coder.keda@gmail.com>
 */
class JoinController
{

    /**
     * 加入房间
     * @param $params
     * @return void
     */
    public function actionRoom($params)
    {
        // 使用模型
        $model             = new JoinForm();
        $model->attributes = $params;
        $model->setScenario('actionRoom');
        // 验证失败
        if (!$model->validate()) {
            return;
        }

        // 保存当前加入的房间
        app()->tcpSession->set('roomid', $model->roomid);

        // 订阅房间的频道
        xgo(function () use ($model) {
            $conn = app()->redisPool->getConnection();
            $conn->subscribe("room_{$model->roomid}", function ($instance, $channel, $message) {
                $frame = new TextFrame([
                    'data' => $message,
                ]);
                app()->ws->push($frame);
            });
            $conn->release();
        });

        // 给当前房间发送消息
        $name    = app()->tcpSession->get('name');
        $message = [
            'result' => [
                'message' => "{$name} 加入 {$model->roomid} 房间.",
            ],
        ];
        $conn    = app()->redisPool->getConnection();
        $conn->publish("room_{$model->roomid}", json_encode($message));
        $conn->release();
    }

}
