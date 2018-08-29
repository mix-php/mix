<?php

namespace apps\httpd\models;

use mix\validators\Validator;

class FileForm extends Validator
{

    /**
     * @var \mix\http\UploadFile
     */
    public $file;

    // 规则
    public function rules()
    {
        return [
            'file' => ['file', 'mimes' => ['image/gif', 'image/jpeg', 'image/png', 'audio/mp3', 'video/mp4'], 'maxSize' => 1024 * 1],
        ];
    }

    // 场景
    public function scenarios()
    {
        return [
            'upload' => ['required' => ['file']],
        ];
    }

    // 消息
    public function messages()
    {
        return [
            'file.mimes'   => '文件类型不支持.',
            'file.maxSize' => '文件大小不能超过1MB.',
        ];
    }

}
