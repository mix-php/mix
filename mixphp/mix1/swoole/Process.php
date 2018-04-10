<?php

namespace mix\swoole;

/**
 * 进程助手类
 * @author 刘健 <coder.liu@qq.com>
 */
class Process
{

    // 使当前进程蜕变为一个守护进程
    public static function daemon($closeStandardInputOutput = true)
    {
        \Swoole\Process::daemon(true, !$closeStandardInputOutput);
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

    // 写入 PID 文件
    public static function writePid($pidFile)
    {
        $pid  = Process::getPid();
        $file = fopen($pidFile, "w+");
        if (flock($file, LOCK_EX)) {
            fwrite($file, $pid);
            flock($file, LOCK_UN);
        } else {
            die("Error writing file '{$pidFile}'" . PHP_EOL);
        }
        fclose($file);
    }

    // 返回当前进程 id
    public static function getPid()
    {
        return getmypid();
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
