<?php

/**
 * 控制器
 * @author 刘健 <code.liu@qq.com>
 */

namespace www\command;

use express\web\Controller;

class SiteController extends Controller
{

    public function actionIndex()
    {
        $param = \Express::$app->request->param();
        return 'hello world';
    }

}
