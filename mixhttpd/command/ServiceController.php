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
        exec('ps -ef | grep mixhttpd', $output, $status);
        if ($status != 0) {
            return '命令执行错误' . PHP_EOL;
        }
        $master = array_shift($output);
        if (strpos($master, 'mixhttpd') !== false && strpos($master, 'master') !== false) {
            return '服务在运行中' . PHP_EOL;
        }
        return \Mix::app()->server->start();
    }

    // 停止服务
    public function actionStop()
    {
        exec('ps -ef | grep mixhttpd | awk \'NR==1{print $2}\' | xargs -n1 kill');
        return '服务停止完成' . PHP_EOL;
    }

}
