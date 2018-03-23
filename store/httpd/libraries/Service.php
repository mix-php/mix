<?php

namespace store\httpd\libraries;

use mix\swoole\Process;

/**
 * 服务类
 * @author 刘健 <coder.liu@qq.com>
 */
class Service
{

    // 获取主进程PID
    public static function getMasterPid()
    {
        $pidFile = empty(\Mix::app()->objects['httpServer']['setting']['pid_file']) ? false : \Mix::app()->objects['httpServer']['setting']['pid_file'];
        if (!$pidFile) {
            die('main.php: [objects.httpServer.setting.pid_file] config item cannot be empty.' . PHP_EOL);
        }
        if (!file_exists($pidFile)) {
            return false;
        }
        $pid = file_get_contents($pidFile);
        if (Process::isRunning($pid)) {
            return $pid;
        }
        return false;
    }

}
