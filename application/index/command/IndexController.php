<?php

namespace index\command;

use mix\console\Controller;

/**
 * 默认控制器
 * @author 刘健 <code.liu@qq.com>
 */
class IndexController extends Controller
{

    public function actionIndex()
    {
        $param = \Mix::app()->request->param();
        return 'Hello World' . PHP_EOL;
    }

}
