<?php

namespace mix\console;

use mix\base\Object;

/**
 * Controller类
 * @author 刘健 <coder.liu@qq.com>
 */
class Controller extends Object
{

    // 使当前进程蜕变为一个守护进程
    public function daemon()
    {
        \swoole_process::daemon(true, true);
        $pid = getmypid();
        return "PID: {$pid}";
    }

}
