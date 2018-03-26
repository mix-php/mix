<?php

namespace store\httpd\commands;

use mix\console\Controller;
use mix\swoole\Process;

/**
 * 工作控制器
 * @author 刘健 <coder.liu@qq.com>
 */
class WorkerController extends Controller
{

    // 重启工作进程
    public function actionReload()
    {
        if ($pid = Process::getMasterPid(\Mix::app()->objects['httpServer']['setting']['pid_file'])) {
            Process::kill($pid, SIGUSR1);
        }
        if (!$pid) {
            return 'mix-httpd is not running.' . PHP_EOL;
        }
        return 'mix-httpd worker process restart completed.' . PHP_EOL;
    }

}
