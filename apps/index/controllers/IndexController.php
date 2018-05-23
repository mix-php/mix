<?php

namespace apps\index\controllers;

use mix\facades\Request;
use mix\http\Controller;
use apps\index\models\IndexForm;

/**
 * 默认控制器
 * @author 刘健 <coder.liu@qq.com>
 */
class IndexController extends Controller
{

    // 默认动作
    public function actionIndex()
    {
        return 'Hello World' . PHP_EOL;
    }

    // API 范例，访问路径：/index/api
    public function actionApi()
    {
        // 使用模型
        $model             = new IndexForm();
        $model->attributes = Request::get() + Request::post();
        $model->setScenario('test');
        if (!$model->validate()) {
            return ['code' => 1, 'message' => '参数格式效验失败', 'data' => $model->getErrors()];
        }
        $model->save();
        // 响应
        app()->response->format = \mix\http\Response::FORMAT_JSON;
        return ['code' => 0, 'message' => 'OK'];
    }

    // WebSite 范例，访问路径：/index/website
    public function actionWebsite()
    {
        // 使用模型
        $model             = new IndexForm();
        $model->attributes = Request::get() + Request::post();
        $model->setScenario('test');
        if (!$model->validate()) {
            return $this->render('web_site_example', ['message' => $model->getError()]);
        }
        $model->save();
        // 响应
        return $this->render('web_site_example', ['message' => '新增成功']);
    }

}
