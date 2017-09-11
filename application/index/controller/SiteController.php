<?php

/**
 * Site控制器类
 * @author 刘健 <code.liu@qq.com>
 */

namespace index\controller;

use mix\web\Controller;

class SiteController extends Controller
{

    public function actionIndex()
    {
        $rows = \Mix::app()->rdb->createCommand("SELECT * FROM `post`")->queryAll();
        return $rows;
    }

}
