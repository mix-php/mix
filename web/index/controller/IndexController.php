<?php

namespace web\index\controller;

use mix\web\Controller;
use web\index\model\IndexForm;

/**
 * 默认控制器
 * @author 刘健 <coder.liu@qq.com>
 */
class IndexController extends Controller
{

    public function actionIndex()
    {
        return 'Hello World' . PHP_EOL;
    }

    // API 范例
    public function actionApiExample()
    {
        // 使用模型
        $model             = new IndexForm();
        $model->attributes = \Mix::app()->request->get() + \Mix::app()->request->post();
        $model->setScenario('test');
        if (!$model->validate()) {
            return ['code' => 1, 'message' => '参数格式效验失败', 'data' => $model->errors];
        }
        $model->save();
        // 响应
        \Mix::app()->response->format = \mix\web\Response::FORMAT_JSON;
        return ['code' => 0, 'message' => 'OK'];
    }

    // WebSite 范例
    public function actionWebSiteExample()
    {
        // 使用模型
        $model             = new IndexForm();
        $model->attributes = \Mix::app()->request->get() + \Mix::app()->request->post();
        $model->setScenario('test');
        if (!$model->validate()) {
            return $this->render('web-site-example', ['message' => '参数格式效验失败', 'errors' => $model->errors]);
        }
        $model->save();
        // 响应
        return $this->render('web-site-example', ['message' => '新增成功']);
    }

}
