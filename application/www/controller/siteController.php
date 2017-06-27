<?php

/**
 * 控制器
 * @author 刘健 <code.liu@qq.com>
 */

namespace www\controller;

use express\web\Controller;

class siteController extends Controller
{
    
    public function actionIndex($request)
    {
        $request = \Express::$app->request->create($request);
        return $request;
    }

    public function actionMyTest()
    {
        return \Express::$app->request->route();
    }

}
