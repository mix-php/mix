<?php

namespace WebSocketd\Commands\Service;

use Mix\Console\Command\AbstractCommand;
use Mix\Console\PidFileHandler;

/**
 * Class BaseCommand
 * @package Httpd\Commands\Service
 */
class BaseCommand extends AbstractCommand
{

    // 提示
    const START_WELCOME = 'Service start successed.';
    const IS_RUNNING = 'Service is running, PID : %d';
    const NOT_RUNNING = 'Service is not running.';
    const EXEC_SUCCESS = 'Command executed successfully.';

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
