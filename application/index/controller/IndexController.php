<?php

/**
 * 默认控制器
 * @author 刘健 <code.liu@qq.com>
 */

namespace index\controller;

use mix\web\Controller;

class IndexController extends Controller
{

    public function actionIndex()
    {
        //$rows = \Mix::app()->rdb->createCommand("SELECT * FROM `post`")->queryAll();
        //return $rows;
        return \Mix::app()->redis->set('test:haha', '11');
    }

}
