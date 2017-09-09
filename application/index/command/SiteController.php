<?php

/**
 * 控制器
 * @author 刘健 <code.liu@qq.com>
 */

namespace index\command;

use mix\console\Controller;

class SiteController extends Controller
{

    public function actionIndex()
    {
        $param = \Mix::app()->request->param();
        return 'Hello World';
    }

}
