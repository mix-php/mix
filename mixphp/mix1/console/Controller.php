<?php

namespace mix\console;

/**
 * Controller类
 * @author 刘健 <coder.liu@qq.com>
 */
class Controller extends \mix\base\Controller
{

    // 使当前进程蜕变为一个守护进程
    public static function daemon($closeInputOutput = false)
    {
        $pid = getmypid();
        echo "PID: {$pid}" . PHP_EOL;
        \Swoole\Process::daemon(true, !$closeInputOutput);
    }

    // 获取控制器名称
    public function getControllerName()
    {
        $class = str_replace('Controller', '', get_class($this));
        return \mix\base\Route::camelToSnake(\mix\base\Route::basename($class), '-');
    }

}
