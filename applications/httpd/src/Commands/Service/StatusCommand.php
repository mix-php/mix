<?php

namespace Httpd\Commands\Service;

/**
 * Status 子命令
 * @author LIUJIAN <coder.keda@gmail.com>
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
            println(self::NOT_RUNNING);
            return;
        }
        println(sprintf(self::IS_RUNNING, $pid));
    }

}
