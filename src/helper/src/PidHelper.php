<?php

namespace Mix\Helper;

/**
 * Class PidHelper
 * @package Mix\Console
 * @author liu,jian <coder.keda@gmail.com>
 */
class PidHelper
{

    /**
     * 写入pid
     * @param string $file
     * @return bool
     */
    public static function write(string $file): bool
    {
        return file_put_contents($file, getmypid(), LOCK_EX) ? true : false;
    }

    /**
     * 读取pid
     * @param string $file
     * @return bool|string
     */
    public static function read(string $file)
    {
        if (!file_exists($file)) {
            return false;
        }
        $pid = file_get_contents($file);
        if (!is_numeric($pid) || !posix_kill($pid, 0)) {
            return false;
        }
        return $pid;
    }

}
