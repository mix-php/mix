<?php

/**
 * 控制器
 * @author 刘健 <code.liu@qq.com>
 */

namespace mixhttpd\command;

use mix\console\Controller;

class WorkerController extends Controller
{

    // 重启工作进程
    public function actionReload()
    {
        exec('ps -ef | grep mixhttpd | awk \'NR==1{print $2}\' | xargs -n1 kill -USR1');
        echo 'mixhttpd worker reloaded' . PHP_EOL;
    }

}
