<?php

namespace Http\Controllers;

use Http\Models\UserForm;
use Mix\Http\Message\Response\HttpResponse;

/**
 * Class UserController
 * @package Http\Controllers
 * @author liu,jian <coder.keda@gmail.com>
 */
class UserController
{

    /**
     * 新增用户
     * @return array
     */
    public function actionCreate()
    {
        app()->response->format = HttpResponse::FORMAT_JSON;

        // 使用模型
        $model             = new UserForm();
        $model->attributes = app()->request->get() + app()->request->post();
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
