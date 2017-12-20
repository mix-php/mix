<?php

namespace httpd\library;

/**
 * 服务类
 * @author 刘健 <coder.liu@qq.com>
 */
class Service
{

    // 获取PID
    public static function getPid()
    {
        $pidFile = \Mix::app()->getRuntimePath() . 'mix-httpd.pid';
        if (!file_exists($pidFile)) {
            return false;
        }
        $pid = file_get_contents($pidFile);
        if (self::isRunning($pid)) {
            return $pid;
        }
        return false;
    }

    // 检查PID是否运行
    public static function isRunning($pid)
    {
        return \swoole_process::kill($pid, 0);
    }

    // kill主进程
    public static function killMaster($pid)
    {
        \swoole_process::kill($pid);
    }

    // 重启工作进程
    public static function reloadWorker($pid)
    {
        \swoole_process::kill($pid, SIGUSR1);
    }

}
