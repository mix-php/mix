<?php

namespace WebSocket\Models;

use Mix\Validate\Validator;

/**
 * Class MessageForm
 * @package WebSocket\Models
 * @author liu,jian <coder.keda@gmail.com>
 */
class MessageForm extends Validator
{

    /**
     * 消息
     * @var string
     */
    public $message;

    /**
     * 规则
     * @return array
     */
    public function rules()
    {
        return [
            'message' => ['string', 'minLength' => 1, 'maxLength' => 300],
        ];
    }

    /**
     * 场景
     * @return array
     */
    public function scenarios()
    {
        return [
            'actionEmit' => ['required' => ['message']],
        ];
    }

}
