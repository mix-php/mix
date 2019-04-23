<?php

namespace Http\Models;

use Mix\Validate\Validator;

/**
 * Class FileForm
 * @package Http\Models
 * @author liu,jian <coder.keda@gmail.com>
 */
class FileForm extends Validator
{

    /**
     * 上传文件对象
     * @var \Mix\Http\Message\UploadFile
     */
    public $file;

    /**
     * 规则
     * @return array
     */
    public function rules()
    {
        return [
            'file' => ['file', 'mimes' => ['image/gif', 'image/jpeg', 'image/png', 'audio/mp3', 'video/mp4'], 'maxSize' => 1024 * 1],
        ];
    }

    /**
     * 场景
     * @return array
     */
    public function scenarios()
    {
        return [
            'upload' => ['required' => ['file']],
        ];
    }

    /**
     * 消息
     * @return array
     */
    public function messages()
    {
        return [
            'file.mimes'   => '文件类型不支持.',
            'file.maxSize' => '文件大小不能超过1MB.',
        ];
    }

}
