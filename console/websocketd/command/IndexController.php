<?php

namespace console\daemon\command;

use mix\console\Controller;

/**
 * 默认控制器
 * @author 刘健 <coder.liu@qq.com>
 */
class IndexController extends Controller
{

    public function actionIndex()
    {
        // 脱离终端
        $this->daemon();
        // 连接redis/mysql等，使用长连接版本的数据库组件，这样组件会自动帮你维护连接不断线
        // ...
        // 循环执行任务
        while (true) {
            // 执行业务代码
            // ...
        }
    }

}
