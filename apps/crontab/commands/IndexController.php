<?php

namespace apps\crontab\commands;

use mix\console\Controller;
use mix\swoole\Process;

/**
 * 默认控制器
 * @author 刘健 <coder.liu@qq.com>
 */
class IndexController extends Controller
{

    // 是否后台运行
    protected $d = false;

    // 执行任务
    public function actionIndex()
    {
        $name = \Mix::app()->request->route('controller') . '/' . \Mix::app()->request->route('action');
        // 蜕变为守护进程
        if ($this->d) {
            Process::daemon();
        }
        // 修改进程名称
        Process::setName('mix-crontab: ' . $name);

        // 模型内使用短连接版本的数据库组件，计划任务都是一次性执行
        $tableModel = new \apps\common\models\TableModel();
        // 执行业务代码
        // ...

        // 响应
        return 'SUCCESS' . PHP_EOL;
    }

}
