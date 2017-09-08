<?php

/**
 * 控制器
 * @author 刘健 <code.liu@qq.com>
 */

namespace mixhttpd\command;

use mix\console\Controller;

class StartController extends Controller
{

    // 启动服务
    public function actionIndex()
    {
        echo 'mixhttpd started' . PHP_EOL;
        \Mix::$app->server->start();
    }

}
