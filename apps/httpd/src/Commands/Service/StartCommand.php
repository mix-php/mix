<?php

namespace Httpd\Commands\Service;

use Mix\Console\CommandLine\Flag;

/**
 * Start 子命令
 * @author 刘健 <coder.liu@qq.com>
 */
class StartCommand extends BaseCommand
{

    // 主函数
    public function main()
    {
        // 获取参数
        $update = Flag::bool(['u', 'update'], false);
        $daemon = Flag::bool(['d', 'daemon'], false);
        // 获取服务信息
        $server  = \Mix\Http\Server::newInstance();
        $pidFile = $server->settings['pid_file'];
        $pid     = $this->getServicePid($pidFile);
        if ($pid) {
            println(sprintf(self::IS_RUNNING, $pid));
            return;
        }
        // 启动服务
        if ($update) {
            $server->settings['max_request'] = 1;
        }
        $server->settings['daemonize'] = $daemon;
        $server->start();
    }

}
