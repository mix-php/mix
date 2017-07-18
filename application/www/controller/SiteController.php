<?php

/**
 * 控制器
 * @author 刘健 <code.liu@qq.com>
 */

namespace www\controller;

use express\web\Controller;

class SiteController extends Controller
{

    public function actionIndex()
    {
        // \Express::$app->response->statusCode = 404;
        // \Express::$app->response->format = \express\web\Response::FORMAT_JSONP;
        // return ['errcode' => 0, 'errmsg' => 'ok'];

        // return $this->render('index', ['name' => 'xiaoliu', 'sex' => 'nan']);

        // \Express::$app->session->set('user', ['name' => 'xiaoliu', 'sex' => 'nan']);
        // var_dump(\Express::$app->session->get());

        // \Express::$app->cookie->set('user', 'xiaoliu');

        \Express::$app->redis->set('user', 'xiaoliu');
        var_dump(\Express::$app->redis->get('user'));

        return 'ExpressPHP V2';
    }

}
