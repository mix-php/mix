<?php

namespace WebSocket\Controllers;

use Mix\Http\Message\Json;
use WebSocket\Models\JoinForm;

/**
 * 加入控制器
 * @author LIUJIAN <coder.keda@gmail.com>
 */
class JoinController
{

    // 加入房间
    public function actionRoom($data, $userinfo)
    {
        // 使用模型
        $model             = new JoinForm();
        $model->attributes = $data;
        $model->setScenario('actionRoom');
        // 验证失败
        if (!$model->validate()) {
            return;
        }

        // 给全部人发广播
        $server = $this->server;
        foreach ($server->fds as $fd => $item) {
            $message = Json::encode(['callback' => 'joinRoom', 'data' => ['message' => "{$userinfo['name']} 加入房间"]]);
            $server->push($fd, $message);
        }

        // 如果只需给当前 fd 回复消息，只需 return 消息即可
        return Json::encode(['callback' => 'joinRoom', 'data' => ['message' => "我 加入房间"]]);
    }

}
