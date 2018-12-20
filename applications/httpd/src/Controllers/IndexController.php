<?php

namespace Httpd\Controllers;

use Mix\Http\Controller;

/**
 * 默认控制器
 * @author LIUJIAN <coder.keda@gmail.com>
 */
class IndexController extends Controller
{

    // 默认动作
    public function actionIndex()
    {
        return 'Hello, World!';
    }

}
