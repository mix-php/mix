<?php

namespace Mix\Helper;

use Mix\Concurrent\Coroutine;

/**
 * ProcessHelper类
 * @author liu,jian <coder.keda@gmail.com>
 */
class ProcessHelper
{

    /**
     * 使当前进程蜕变为一个守护进程
     * @param bool $noclose
     * @param bool $nochdir
     * @return bool
     */
    public static function daemon(bool $noclose = false, bool $nochdir = true)
    {
        if (PhpHelper::isMac() && \Swoole\Coroutine::getCid() > -1) {
            throw new \Swoole\Exception('MacOS unsupport fork in coroutine, please use it before the Swoole\Coroutine\Scheduler start.');
        }
        return \Swoole\Process::daemon($nochdir, $noclose);
    }

    /**
     * 设置进程标题
     * @param $title
     * @return bool
     */
    public static function setProcessTitle($title)
    {
        if (PhpHelper::isMac() || PhpHelper::isWin()) {
            return false;
        }
        if (!function_exists('cli_set_process_title')) {
            return false;
        }
        return @cli_set_process_title($title);
    }

    /**
     * kill进程
     * @param $pid
     * @param int $signal
     * @return bool
     */
    public static function kill($pid, $signal = SIGTERM)
    {
        return posix_kill($pid, $signal);
    }

    /**
     * 返回当前进程ID
     * @return int
     */
    public static function getPid()
    {
        return getmypid();
    }

    /**
     * 批量设置异步信号监听
     * @param $signals array
     * @param $callback callable|null
     */
    public static function signal($signals, $callback)
    {
        foreach ($signals as $signal) {
            if (is_null($callback)) {
                \Swoole\Process::signal($signal, null);
                continue;
            }
            \Swoole\Process::signal($signal, function ($signal) use ($callback) {
                // 创建协程
                Coroutine::create($callback, $signal);
            });
        }
    }

}
