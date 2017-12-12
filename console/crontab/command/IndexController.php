<?php

namespace console\crontab\command;

use mix\console\Controller;

/**
 * 默认控制器
 * @author 刘健 <coder.liu@qq.com>
 */
class IndexController extends Controller
{

    public function actionIndex()
    {
        // 蜕变为守护进程
        self::daemon();
        // 模型内使用短连接版本的数据库组件，计划任务都是一次性执行
        $tableModel = new \web\common\model\TableModel();
        // 执行业务代码
        // ...
        // 响应
        return 'SUCCESS' . PHP_EOL;
    }

}
