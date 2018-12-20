<?php

namespace Httpd\Controllers;

use Mix\Http\Controller;

/**
 * 视图范例
 * @author LIUJIAN <coder.keda@gmail.com>
 */
class ProfileController extends Controller
{

    // 布局
    public $layout = 'main';

    // 默认动作
    public function actionIndex()
    {
        $data = [
            'name'    => '小明',
            'age'     => 18,
            'friends' => ['小红', '小花', '小飞'],
        ];
        return $this->render('index', $data);
    }

}
