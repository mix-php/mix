<?php

namespace apps\index\controllers;

use apps\index\models\FileForm;
use mix\facades\Request;
use mix\http\Controller;

class FileController extends Controller
{

    // 文件上传
    public function actionUpload()
    {
        app()->response->format = \mix\http\Response::FORMAT_JSON;

        // 使用模型
        $model             = new FileForm();
        $model->attributes = Request::post();
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
