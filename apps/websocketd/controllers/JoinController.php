<?php

namespace apps\websocketd\controllers;

use mix\websocket\Controller;
use mix\web\Json;
use apps\websocketd\models\JoinForm;

/**
 * 加入控制器
 * @author 刘健 <coder.liu@qq.com>
 */
class JoinController extends Controller
{

    // 加入房间
    public function actionRoom($data, $userinfo)
    {
        // 使用模型
        $model             = new JoinForm();
        $model->attributes = $data;
        $model->setScenario('room');
        // 验证失败
        if (!$model->validate()) {
            return null;
        }

        // 给全部人发广播
        $server = $this->getServer();
        foreach ($server->table as $fd => $item) {
            $message = Json::encode(['error_code' => 0, 'error_message' => "{$userinfo['name']} 加入房间"]);
            $server->push($fd, $message);
        }

        // 如果只需给当前fd回复消息，只需 return 消息即可
        return Json::encode(['error_code' => 0, 'error_message' => "我 加入房间"]);
    }

}
