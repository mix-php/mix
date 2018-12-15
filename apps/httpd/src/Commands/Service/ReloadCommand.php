<?php

namespace Httpd\Commands\Service;

use Mix\Helpers\ProcessHelper;

/**
 * Reload 子命令
 * @author 刘健 <coder.liu@qq.com>
 */
class ReloadCommand extends BaseCommand
{

    // 主函数
    public function main()
    {
        // 获取服务信息
        $server  = \Mix\Http\Server::newInstance();
        $pidFile = $server->settings['pid_file'];
        $pid     = $this->getServicePid($pidFile);
        if (!$pid) {
            echo self::NOT_RUNNING;
            return;
        }
        // 重启子进程
        ProcessHelper::kill($pid, SIGUSR1);
        echo self::EXEC_SUCCESS;
    }

}
