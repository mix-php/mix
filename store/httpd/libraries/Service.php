<?php

namespace store\httpd\libraries;

/**
 * 服务类
 * @author 刘健 <coder.liu@qq.com>
 */
class Service
{

    // 获取PID
    public static function getPid()
    {
        $pidFile = '/var/run/mix-httpd.pid';
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
        return \Swoole\Process::kill($pid, 0);
    }

    // kill主进程
    public static function killMaster($pid)
    {
        \Swoole\Process::kill($pid);
    }

    // 重启工作进程
    public static function reloadWorker($pid)
    {
        \Swoole\Process::kill($pid, SIGUSR1);
    }

}
