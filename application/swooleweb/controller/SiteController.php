<?php

/**
 * Site控制器类
 * @author 刘健 <code.liu@qq.com>
 */

namespace swooleweb\controller;

use mix\web\Controller;

class SiteController extends Controller
{

    public function actionIndex()
    {
        return 'MixPHP V1';
    }

}
