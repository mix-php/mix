<?php

namespace Apps\Httpd\Commands;

use Mix\Console\Command;
use Mix\Facades\Output;
use Mix\Helpers\ProcessHelper;

/**
 * Service 命令
 * @author 刘健 <coder.liu@qq.com>
 */
class ServiceCommand extends Command
{

    // 提示
    const IS_RUN = "Service is running, PID : %d";
    const NOT_RUN = "Service is not running.";
    const EXEC_SUCCESS = "Command executed successfully.";

    // 是否后台运行
    public $daemon = false;

    // 是否热更新
    public $update = false;

    // 选项配置
    public function options()
    {
        return ['daemon', 'update'];
    }

    // 选项别名配置
    public function optionAliases()
    {
        return ['d' => 'daemon', 'u' => 'update'];
    }

    // 启动服务
    public function actionStart()
    {
        $server = \Mix\Http\Server::newInstance();
        $pid    = ProcessHelper::readPidFile($server->settings['pid_file']);
        if ($pid) {
            Output::writeln(sprintf(self::IS_RUN, $pid));
            return;
        }
        if ($this->update) {
            $server->settings['max_request'] = 1;
        }
        $server->settings['daemonize'] = $this->daemon;
        $server->start();
    }

    // 停止服务
    public function actionStop($restart = false)
    {
        $server = \Mix\Http\Server::newInstance();
        $pid    = ProcessHelper::readPidFile($server->settings['pid_file']);
        if (!$pid) {
            $restart or Output::writeln(self::NOT_RUN);
            return;
        }
        ProcessHelper::kill($pid);
        while (ProcessHelper::isRunning($pid)) {
            // 等待进程退出
            usleep(100000);
        }
        $restart or Output::writeln(self::EXEC_SUCCESS);
    }

    // 重启服务
    public function actionRestart()
    {
        $this->actionStop(true);
        $this->actionStart();
        // 返回退出码
        return;
    }

    // 重启工作进程
    public function actionReload()
    {
        $server = \Mix\Http\Server::newInstance();
        $pid    = ProcessHelper::readPidFile($server->settings['pid_file']);
        if (!$pid) {
            Output::writeln(self::NOT_RUN);
            return;
        }
        ProcessHelper::kill($pid, SIGUSR1);
        Output::writeln(self::EXEC_SUCCESS);
    }

    // 查看服务状态
    public function actionStatus()
    {
        $server = \Mix\Http\Server::newInstance();
        $pid    = ProcessHelper::readPidFile($server->settings['pid_file']);
        if (!$pid) {
            Output::writeln(self::NOT_RUN);
            return;
        }
        Output::writeln(sprintf(self::IS_RUN, $pid));
    }

}
