<?php

namespace console\daemon\command;

use mix\console\Controller;

/**
 * 这是一个单进程守护进程的范例
 * @author 刘健 <coder.liu@qq.com>
 */
class SingleController extends Controller
{

    // 是否后台运行
    protected $d = false;

    // 启动守护进程
    public function actionStart()
    {
        // 蜕变为守护进程
        if ($this->d) {
            self::daemon();
        }
        // 模型内使用长连接版本的数据库组件，这样组件会自动帮你维护连接不断线
        $tableModel = new \console\common\model\TableModel();
        // 循环执行任务
        while (true) {
            // 执行业务代码
            // ...
        }
    }

}
