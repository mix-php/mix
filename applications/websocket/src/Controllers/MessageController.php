<?php

namespace WebSocket\Controllers;

use WebSocket\Models\MessageForm;

/**
 * 消息控制器
 * @author LIUJIAN <coder.keda@gmail.com>
 */
class MessageController
{

    // 加入房间
    public function actionEmit($data, $userinfo)
    {
        // 使用模型
        $model             = new MessageForm();
        $model->attributes = $data;
        $model->setScenario('actionEmit');
        // 验证失败
        if (!$model->validate()) {
            return;
        }

        // 通过消息队列给指定用户id发消息
        app()->redis->publish('emit_to_' . $model->to_uid, $model->message);
    }

}
