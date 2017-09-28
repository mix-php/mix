<?php

/**
 * 控制器
 * @author 刘健 <coder.liu@qq.com>
 */

namespace mixhttpd\command;

use mix\console\Controller;

class WorkerController extends Controller
{

    // 重启工作进程
    public function actionReload()
    {
        exec('ps -ef | grep mixhttpd | awk \'NR==1{print $2}\' | xargs -n1 kill -USR1', $output, $status);
        if ($status != 0) {
            return '命令执行错误' . PHP_EOL;
        }
        echo '工作进程重启完成' . PHP_EOL;
    }

}
