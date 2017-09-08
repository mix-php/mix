<?php

/**
 * 控制器
 * @author 刘健 <code.liu@qq.com>
 */

namespace mixhttpd\command;

use mix\console\Controller;

class ServiceController extends Controller
{

    // 启动服务
    public function actionStart()
    {
        return \Mix::app()->server->start();
    }

    // 停止服务
    public function actionStop()
    {
        exec('ps -ef | grep mixhttpd | awk \'NR==1{print $2}\' | xargs -n1 kill');
        return 'mixhttpd stoped' . PHP_EOL;
    }

}
