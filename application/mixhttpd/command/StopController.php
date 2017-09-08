<?php

/**
 * 控制器
 * @author 刘健 <code.liu@qq.com>
 */

namespace mixhttpd\command;

use mix\console\Controller;

class StopController extends Controller
{

    // 停止服务
    public function actionIndex()
    {
        exec('ps -ef | grep mixhttpd | awk \'NR==1{print $2}\' | xargs -n1 kill');
        return 'mixhttpd stoped' . PHP_EOL;
    }

}
