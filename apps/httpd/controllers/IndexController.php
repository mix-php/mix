<?php

namespace apps\httpd\controllers;

use mix\http\Controller;

/**
 * 默认控制器
 * @author 刘健 <coder.liu@qq.com>
 */
class IndexController extends Controller
{

    // 默认动作
    public function actionIndex()
    {
        return 'Hello, World!';
    }

}
