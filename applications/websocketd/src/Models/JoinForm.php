<?php

namespace WebSocketd\Models;

use Mix\Validators\Validator;

/**
 * Join 表单模型类
 * @author LIUJIAN <coder.keda@gmail.com>
 */
class JoinForm extends Validator
{

    public $room_id;

    // 规则
    public function rules()
    {
        return [
            'room_id' => ['integer', 'unsigned' => true, 'minLength' => 1, 'maxLength' => 10],
        ];
    }

    // 场景
    public function scenarios()
    {
        return [
            'actionRoom' => ['required' => ['room_id']],
        ];
    }

}
