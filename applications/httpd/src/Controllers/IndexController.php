<?php

namespace Httpd\Controllers;

use Mix\Http\AbstractController;

/**
 * 默认控制器
 * @author LIUJIAN <coder.keda@gmail.com>
 */
class IndexController extends AbstractController
{

    // 默认动作
    public function actionIndex()
    {
        return 'Hello, World!';
    }

}
