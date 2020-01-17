<?php

namespace Mix\Log;

use Mix\Console\CommandLine\Color;

/**
 * Class StdoutHandler
 * @package Mix\Log
 * @author liu,jian <coder.keda@gmail.com>
 */
class StdoutHandler implements LoggerHandlerInterface
{

    /**
     * 处理日志
     * @param $level
     * @param $message
     */
    public function handle($level, $message)
    {
        // win系统普通打印
        if (!(stripos(PHP_OS, 'Darwin') !== false) && stripos(PHP_OS, 'WIN') !== false) {
            $message = preg_replace("/\\e\[[0-9]+m/", '', $message); // 过滤颜色
            echo $message;
            return;
        }
        // 带颜色打印
        echo $message;
    }

}
