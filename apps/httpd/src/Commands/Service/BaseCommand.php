<?php

namespace Apps\Httpd\Commands\Service;

use Mix\Console\Command;
use Mix\Console\PidFileHandler;

/**
 * Class BaseCommand
 * @package Apps\Httpd\Commands\Service
 */
class BaseCommand extends Command
{

    // 提示
    const IS_RUNNING = "Service is running, PID : %d" . PHP_EOL;
    const NOT_RUNNING = "Service is not running." . PHP_EOL;
    const EXEC_SUCCESS = "Command executed successfully." . PHP_EOL;

    /**
     * 获取pid
     * @param $pidFile
     * @return bool|string
     */
    public function getServicePid($pidFile)
    {
        $handler = new PidFileHandler(['pidFile' => $pidFile]);
        return $handler->read();
    }

}
