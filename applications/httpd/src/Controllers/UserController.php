<?php

namespace Httpd\Controllers;

use Httpd\Models\UserForm;
use Mix\Facades\Request;
use Mix\Http\Controller;

/**
 * API接口范例
 * @author 刘健 <coder.liu@qq.com>
 */
class UserController extends Controller
{

    // 新增用户
    public function actionCreate()
    {
        app()->response->format = \Mix\Http\Response::FORMAT_JSON;

        // 使用模型
        $model             = new UserForm();
        $model->attributes = Request::get() + Request::post();
        $model->setScenario('create');
        if (!$model->validate()) {
            return ['code' => 1, 'message' => 'FAILED', 'data' => $model->getErrors()];
        }

        // 执行保存数据库
        // ...

        // 响应
        return ['code' => 0, 'message' => 'OK'];
    }

}
