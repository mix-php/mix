<?php

/**
 * 控制器
 * @author 刘健 <code.liu@qq.com>
 */

namespace www\controller;

use express\web\Controller;
use express\web\Request;

class siteController extends Controller
{
    
    public function actionIndex($request)
    {
        $request = Request::create($request);
        return $request;
    }

    public function actionMyTest()
    {
        return \Express::$app->request->route();
    }

}
