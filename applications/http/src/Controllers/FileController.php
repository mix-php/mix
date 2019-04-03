<?php

namespace Http\Controllers;

use Http\Models\FileForm;
use Mix\Http\Message\Response\HttpResponse;

/**
 * Class FileController
 * @package Http\Controllers
 * @author liu,jian <coder.keda@gmail.com>
 */
class FileController
{

    /**
     * 文件上传
     * @return array
     */
    public function actionUpload()
    {
        app()->response->format = HttpResponse::FORMAT_JSON;

        // 使用模型
        $model             = new FileForm();
        $model->attributes = app()->request->post();
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
