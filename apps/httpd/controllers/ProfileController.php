<?php

namespace apps\httpd\controllers;

use mix\http\Controller;

/**
 * 视图范例
 * @author 刘健 <coder.liu@qq.com>
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
