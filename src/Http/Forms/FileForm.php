<?php

namespace App\Http\Forms;

use Mix\Validate\Validator;
use Psr\Http\Message\UploadedFileInterface;

/**
 * Class FileForm
 * @package App\Http\Forms
 * @author liu,jian <coder.keda@gmail.com>
 */
class FileForm extends Validator
{

    /**
     * @var UploadedFileInterface
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
            'upload' => ['required' => [], 'optional' => ['file']],
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
