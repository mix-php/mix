<?php

namespace Console\Commands;

use Mix\Console\Command;
use Mix\Core\Timer;

/**
 * 定时器范例
 * @author LIUJIAN <coder.keda@gmail.com>
 */
class TimerCommand extends Command
{

    /**
     * 主函数
     */
    public function main()
    {
        // 一次性定时
        Timer::new()->after(1000, function () {
            println(time());
        });

        // 持续定时
        $timer = new Timer();
        $timer->tick(1000, function () {
            println(time());
        });

        // 停止定时
        Timer::new()->after(10000, function () use ($timer) {
            $timer->clear();
        });
    }

}
