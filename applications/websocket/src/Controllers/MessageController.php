<?php

namespace WebSocket\Controllers;

use Mix\Helper\JsonHelper;
use Mix\WebSocket\Frame\TextFrame;
use WebSocket\Models\MessageForm;

/**
 * Class MessageController
 * @package WebSocket\Controllers
 * @author liu,jian <coder.keda@gmail.com>
 */
class MessageController
{

    /**
     * 发送消息
     * @param $params
     * @param $id
     */
    public function actionEmit($params, $id)
    {
        // 使用模型
        $model             = new MessageForm();
        $model->attributes = $params;
        $model->setScenario('actionEmit');
        // 验证失败
        if (!$model->validate()) {
            $response = new TextFrame([
                'data' => JsonHelper::encode([
                    'result' => [
                        'message' => $model->getError(),
                    ],
                    'id'     => $id,
                ], JSON_UNESCAPED_UNICODE),
            ]);
            app()->ws->push($response);
            return;
        }

        // 获取加入的房间id
        $roomid = app()->tcpSession->get('roomid');
        if (empty($roomid)) {
            $response = new TextFrame([
                'data' => JsonHelper::encode([
                    'result' => [
                        'message' => "你没有加入任何房间，请先加入一个房间.",
                    ],
                    'id'     => $id,
                ], JSON_UNESCAPED_UNICODE),
            ]);
            app()->ws->push($response);
            return;
        }

        // 给当前加入的房间发送消息
        $response = JsonHelper::encode([
            'result' => [
                'message' => $model->message,
            ],
            'id'     => $id,
        ], JSON_UNESCAPED_UNICODE);
        $conn     = app()->redisPool->getConnection();
        $conn->publish("room_{$roomid}", $response);
        $conn->release();
    }

}
