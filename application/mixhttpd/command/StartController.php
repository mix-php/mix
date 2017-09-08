<?php

/**
 * 控制器
 * @author 刘健 <code.liu@qq.com>
 */

namespace mixhttpd\command;

use mix\web\Controller;

class StartController extends Controller
{

    public function actionIndex()
    {
        \Mix::$app->server->start();
        return 'mixhttpd started' . PHP_EOL;
    }

}
