<?php

namespace App\Http\Controllers;

use App\Http\Helpers\SendHelper;
use App\Http\Forms\FileForm;
use Mix\Http\Message\ServerRequest;
use Mix\Http\Message\Response;

/**
 * Class FileController
 * @package App\Http\Controllers
 * @author liu,jian <coder.keda@gmail.com>
 */
class FileController
{

    /**
     * Upload
     * @param ServerRequest $request
     * @param Response $response
     * @return Response
     */
    public function upload(ServerRequest $request, Response $response)
    {
        // 使用表单验证器
        $form = new FileForm($request->getAttributes(), $request->getUploadedFiles());
        $form->setScenario('upload');
        if (!$form->validate()) {
            $content = ['code' => 1, 'message' => 'FAILED', 'data' => $form->getErrors()];
            return SendHelper::json($response, $content);
        }

        // 保存文件
        if ($form->file) {
            $targetPath = app()->basePath . '/runtime/uploads/' . date('Ymd') . '/' . $form->file->getClientFilename();
            $form->file->moveTo($targetPath);
        }

        // 响应
        $content = ['code' => 0, 'message' => 'OK'];
        return SendHelper::json($response, $content);
    }

}
