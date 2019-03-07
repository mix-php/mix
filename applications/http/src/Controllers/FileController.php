<?php

namespace Http\Controllers;

use Http\Models\FileForm;
use Mix\Http\Message\Request;
use Mix\Http\Message\Response;

/**
 * Class FileController
 * @package Http\Controllers
 * @author LIUJIAN <coder.keda@gmail.com>
 */
class FileController
{

    /**
     * 文件上传
     * @param Request $request
     * @param Response $response
     * @return array
     */
    public function actionUpload(Request $request, Response $response)
    {
        app()->response->format = \Mix\Http\Message\Response::FORMAT_JSON;

        // 使用模型
        $model = new FileForm();
        $model->attributes = $request->post();
        $model->setScenario('upload');
        if (!$model->validate()) {
            return ['code' => 1, 'message' => 'FAILED', 'data' => $model->getErrors()];
        }

        // 保存文件
        $filename = app()->getPublicPath() . '/uploads/' . date('Ymd') . '/' . $model->file->getRandomFileName();
        $model->file->saveAs($filename);

        // 响应
        return ['code' => 0, 'message' => 'OK'];
    }

}
