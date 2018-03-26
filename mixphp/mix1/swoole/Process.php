<?php

namespace mix\swoole;

/**
 * 进程助手类
 * @author 刘健 <coder.liu@qq.com>
 */
class Process
{

    // 使当前进程蜕变为一个守护进程
    public static function daemon($closeInputOutput = false)
    {
        \Swoole\Process::daemon(true, !$closeInputOutput);
        $pid = getmypid();
        echo "PID: {$pid}" . PHP_EOL;
    }
    
    // 设置进程名称
    public static function setName($name)
    {
        if (stristr(PHP_OS, 'DAR')) {
            return;
        }
        if (function_exists('cli_set_process_title')) {
            cli_set_process_title($name);
        } else if (function_exists('swoole_set_process_name')) {
            swoole_set_process_name($name);
        }
    }

    // 检查 PID 是否运行
    public static function isRunning($pid)
    {
        return \Swoole\Process::kill($pid, 0);
    }

    // kill 进程
    public static function kill($pid, $signal = null)
    {
        if (is_null($signal)) {
            \Swoole\Process::kill($pid);
        } else {
            \Swoole\Process::kill($pid, $signal);
        }
    }

    // 返回当前进程 id
    public static function getPid()
    {
        return posix_getpid();
    }

    // 返回主进程 id
    public static function getMasterPid($pidFile)
    {
        if (!file_exists($pidFile)) {
            return false;
        }
        $pid = file_get_contents($pidFile);
        if (self::isRunning($pid)) {
            return $pid;
        }
        return false;
    }

}
