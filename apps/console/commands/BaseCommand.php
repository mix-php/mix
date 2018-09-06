<?php

namespace apps\console\commands;

use mix\console\Command;
use mix\helpers\ProcessHelper;

/**
 * 命令基类，处理公共逻辑部分
 * @author 刘健 <coder.liu@qq.com>
 */
class BaseCommand extends Command
{

    // 是否后台运行
    public $daemon = false;

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

    // 执行任务
    public function actionExec()
    {
        // 蜕变为守护进程
        if ($this->daemon) {
            ProcessHelper::daemon();
        }
        // 修改进程标题
        ProcessHelper::setTitle("mix-console: {$this->programName}");
    }

}
