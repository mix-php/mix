<?php

namespace Httpd\Commands\Service;

use Mix\Helpers\ProcessHelper;

/**
 * Status 子命令
 * @author 刘健 <coder.liu@qq.com>
 */
class StatusCommand extends BaseCommand
{

    // 主函数
    public function main()
    {
        $server  = \Mix\Http\Server::newInstance();
        $pidFile = $server->settings['pid_file'];
        $pid     = $this->getServicePid($pidFile);
        if (!$pid) {
            echo self::NOT_RUNNING;
            return;
        }
        printf(self::IS_RUNNING, $pid);
    }

}
