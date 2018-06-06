<?php

namespace apps\daemon\commands;

use mix\console\Command;
use mix\console\ExitCode;
use mix\facades\Output;
use mix\process\Process;

/**
 * 命令基类，统一处理 [start/stop/restart/status] 方法的公共逻辑部分
 * @author 刘健 <coder.liu@qq.com>
 */
class BaseCommand extends Command
{

    // 是否后台运行
    public $daemon = false;

    // PID 文件
    protected $pidFile = '';

    // 程序名称
    protected $programName = '';

    // 选项配置
    public function options()
    {
        return ['daemon'];
    }

    // 选项别名配置
    public function optionAliases()
    {
        return ['d' => 'daemon'];
    }

    // 启动
    public function actionStart()
    {
        // 重复启动处理
        if ($pid = Process::getMasterPid($this->pidFile)) {
            Output::writeln("mix-daemon '{$this->programName}' is running, PID : {$pid}.");
            // 返回
            return false;
        }
        // 启动提示
        Output::writeln("mix-daemon '{$this->programName}' start successed.");
        // 蜕变为守护进程
        if ($this->daemon) {
            Process::daemon();
        }
        // 写入 PID 文件
        Process::writePid($this->pidFile);
        // 返回
        return true;
    }

    // 停止
    public function actionStop()
    {
        if ($pid = Process::getMasterPid($this->pidFile)) {
            Process::kill($pid);
            while (Process::isRunning($pid)) {
                // 等待进程退出
                usleep(100000);
            }
            Output::writeln("mix-daemon '{$this->programName}' stop completed.");
        } else {
            Output::writeln("mix-daemon '{$this->programName}' is not running.");
        }
        // 返回退出码
        return ExitCode::OK;
    }

    // 重启
    public function actionRestart()
    {
        $this->actionStop();
        $this->actionStart();
        // 返回退出码
        return ExitCode::OK;
    }

    // 查看状态
    public function actionStatus()
    {
        if ($pid = Process::getMasterPid($this->pidFile)) {
            Output::writeln("mix-daemon '{$this->programName}' is running, PID : {$pid}.");
        } else {
            Output::writeln("mix-daemon '{$this->programName}' is not running.");
        }
        // 返回退出码
        return ExitCode::OK;
    }

}
