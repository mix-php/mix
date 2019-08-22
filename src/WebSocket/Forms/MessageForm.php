<?php

namespace App\WebSocket\Forms;

use Mix\Validate\Validator;

/**
 * Class MessageForm
 * @package App\WebSocket\Forms
 * @author liu,jian <coder.keda@gmail.com>
 */
class MessageForm extends Validator
{

    /**
     * @var string
     */
    public $text;

    /**
     * 规则
     * @return array
     */
    public function rules()
    {
        return [
            'text' => ['string', 'minLength' => 1, 'maxLength' => 300],
        ];
    }

    /**
     * 场景
     * @return array
     */
    public function scenarios()
    {
        return [
            'emit' => ['required' => ['text']],
        ];
    }

}
