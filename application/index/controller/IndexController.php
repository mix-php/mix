<?php

namespace index\controller;

use mix\web\Controller;

/**
 * 默认控制器
 * @author 刘健 <coder.liu@qq.com>
 */
class IndexController extends Controller
{

    public function actionIndex()
    {
        //return \Mix::app()->rdb->createCommand("SELECT * FROM `post`")->queryAll();
        return 'Hello World' . PHP_EOL;
    }

}
