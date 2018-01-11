<?php

namespace console\websocketd\controller;

/**
 * 加入控制器
 * @author 刘健 <coder.liu@qq.com>
 */
class JoinController
{

    // 加入房间
    public function actionRoom($data, $userinfo)
    {
        // 处理业务
        // ...
        // 响应
        return json_encode(['error_code' => 0, 'data' => $userinfo]);
    }

}
