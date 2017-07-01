<?php

/**
 * 控制器
 * @author 刘健 <code.liu@qq.com>
 */

namespace www\controller;

use express\web\Controller;

class siteController extends Controller
{

    public function actionIndex()
    {
        \Express::$app->response->statusCode = 404;
        \Express::$app->response->format = \express\web\Response::FORMAT_XML;
        return ['errcode' => 0, 'errmsg' => 'ok'];
    }

}
