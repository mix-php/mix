<?php

namespace Http\Controllers;

use Http\Models\UserForm;
use Mix\Http\Message\Request;
use Mix\Http\Message\Response;

/**
 * Class UserController
 * @package Http\Controllers
 * @author LIUJIAN <coder.keda@gmail.com>
 */
class UserController
{

    /**
     * 新增用户
     * @param Request $request
     * @param Response $response
     * @return array
     */
    public function actionCreate(Request $request, Response $response)
    {
        $response->format = \Mix\Http\Message\Response::FORMAT_JSON;

        // 使用模型
        $model = new UserForm();
        $model->attributes = $request->get() + $request->post();
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
