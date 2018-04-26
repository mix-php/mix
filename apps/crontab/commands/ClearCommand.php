<?php

namespace apps\crontab\commands;

use mix\console\Command;
use mix\console\ExitCode;
use mix\console\Input;
use mix\console\Output;
use mix\swoole\Process;

/**
 * Clear 命令
 * @author 刘健 <coder.liu@qq.com>
 */
class ClearCommand extends Command
{

    // 是否后台运行
    public $daemon = false;

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

    // 执行任务
    public function actionExec()
    {
        // 蜕变为守护进程
        if ($this->daemon) {
            Process::daemon();
        }
        // 修改进程名称
        Process::setName('mix-crontab: ' . $this->input->getCommandName());

        // 模型内使用短连接版本的数据库组件，计划任务都是一次性执行
        $tableModel = new \apps\common\models\TableModel();
        // 执行业务代码
        // ...

        // 响应
        $this->output->writeln('SUCCESS');
        // 返回退出码
        return ExitCode::OK;
    }

}
