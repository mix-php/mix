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
        // 脱离终端
        $this->daemon();
        // 执行业务代码
        // ...
        // 响应
        return 'SUCCESS' . PHP_EOL;
    }

}
