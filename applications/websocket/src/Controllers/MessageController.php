<?php

namespace WebSocket\Controllers;

use Mix\WebSocket\Frame\TextFrame;
use WebSocket\Models\MessageForm;

/**
 * Class MessageController
 * @package WebSocket\Controllers
 * @author LIUJIAN <coder.keda@gmail.com>
 */
class MessageController
{

    /**
     * 发送消息
     * @param $params
     */
    public function actionEmit($params)
    {
        // 使用模型
        $model             = new MessageForm();
        $model->attributes = $params;
        $model->setScenario('actionEmit');
        // 验证失败
        if (!$model->validate()) {
            return;
        }

        // 获取加入的房间id
        $roomid = app()->tcpSession->get('roomid');
        if (empty($roomid)) {
            $message = [
                'result' => [
                    'message' => "你没有加入任何房间，请先加入一个房间.",
                ],
            ];
            $frame   = new TextFrame([
                'data' => json_encode($message),
            ]);
            app()->ws->push($frame);
            return;
        }

        // 给当前加入的房间发送消息
        $message = [
            'result' => [
                'message' => $model->message,
            ],
        ];
        $conn    = app()->redisPool->getConnection();
        $conn->publish("room_{$roomid}", json_encode($message));
        $conn->release();
    }

}
