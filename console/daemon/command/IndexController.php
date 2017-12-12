<?php

namespace console\daemon\command;

use mix\console\Controller;

/**
 * 默认控制器
 * 这是一个单进程守护进程的范例
 * @author 刘健 <coder.liu@qq.com>
 */
class IndexController extends Controller
{

    public function actionIndex()
    {
        // 蜕变为守护进程
        self::daemon();
        // 模型内使用长连接版本的数据库组件，这样组件会自动帮你维护连接不断线
        $tableModel = new \web\common\model\TableModel();
        // 循环执行任务
        while (true) {
            // 执行业务代码
            // ...
        }
    }

}
