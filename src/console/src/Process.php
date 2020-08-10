<?php

namespace Mix\Console;

/**
 * Class Process
 * @package Mix\Console
 */
class Process
{

    /**
     * 使当前进程蜕变为一个守护进程
     * @param bool $noclose
     * @param bool $nochdir
     * @return bool
     */
    public static function daemon(bool $noclose = false, bool $nochdir = true)
    {
        if (stripos(PHP_OS, 'Darwin') !== false && \Swoole\Coroutine::getCid() > -1) {
            throw new \Swoole\Exception('MacOS unsupport fork in coroutine, please use it before the Swoole\Coroutine\Scheduler start.');
        }
        return \Swoole\Process::daemon($nochdir, $noclose);
    }

    /**
     * kill进程
     * @param int $pid
     * @param int $signal
     * @return bool
     */
    public static function kill(int $pid, int $signal = SIGTERM)
    {
        return posix_kill($pid, $signal);
    }

}
