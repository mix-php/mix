<?php

namespace WebSocketd\Commands\Service;

use Mix\Console\CommandLine\Flag;
use Mix\Helpers\ProcessHelper;

/**
 * Restart 子命令
 * @author 刘健 <coder.liu@qq.com>
 */
class RestartCommand extends StartCommand
{

    // 主函数
    public function main()
    {
        // 获取服务信息
        $server  = \Mix\Http\Server::newInstance();
        $pidFile = $server->settings['pid_file'];
        $pid     = $this->getServicePid($pidFile);
        if (!$pid) {
            println(self::NOT_RUNNING);
            return;
        }
        // 停止服务
        ProcessHelper::kill($pid);
        while (ProcessHelper::kill($pid, 0)) {
            // 等待进程退出
            usleep(100000);
        }
        // 启动服务
        parent::main();
    }

}
