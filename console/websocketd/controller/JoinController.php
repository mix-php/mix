<?php

namespace console\websocketd\controller;

use mix\websocket\Controller;
use console\websocketd\model\JoinForm;
use mix\web\Json;

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
        // 验证成功
        if ($model->validate()) {
            // 给全部人发广播
            $server = $this->getServer();
            foreach ($server->table as $fd => $item) {
                $message = Json::encode(['error_code' => 0, 'error_message' => "{$userinfo['name']} 加入房间"]);
                $server->push($fd, $message);
            }
        }
    }

}
