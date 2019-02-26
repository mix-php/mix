<?php

namespace WebSocket\Models;

use Mix\Validators\Validator;

/**
 * Message 表单模型类
 * @author LIUJIAN <coder.keda@gmail.com>
 */
class MessageForm extends Validator
{

    public $to_uid;
    public $message;

    // 规则
    public function rules()
    {
        return [
            'to_uid'  => ['integer', 'unsigned' => true, 'minLength' => 1, 'maxLength' => 10],
            'message' => ['string', 'minLength' => 1, 'maxLength' => 300],
        ];
    }

    // 场景
    public function scenarios()
    {
        return [
            'actionEmit' => ['required' => ['to_uid', 'message']],
        ];
    }

}
