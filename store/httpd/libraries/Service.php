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
        $pidFile = '/var/run/mix-httpd.pid';
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
