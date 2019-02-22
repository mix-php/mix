<?php

namespace Httpd\Controllers;

use Httpd\Models\FileForm;

/**
 * 文件上传范例
 * @author LIUJIAN <coder.keda@gmail.com>
 */
class FileController
{

    // 文件上传
    public function actionUpload()
    {
        app()->response->format = \Mix\Http\Message\Response::FORMAT_JSON;

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
