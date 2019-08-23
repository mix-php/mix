<?php

namespace App\WebSocket\Forms;

use Mix\Validate\Validator;

/**
 * Class JoinForm
 * @package App\WebSocket\Forms
 * @author liu,jian <coder.keda@gmail.com>
 */
class JoinForm extends Validator
{

    /**
     * @var string
     */
    public $roomId;

    /**
     * @var string
     */
    public $name;

    /**
     * 规则
     * @return array
     */
    public function rules()
    {
        return [
            'roomId' => ['integer', 'unsigned' => true, 'minLength' => 1, 'maxLength' => 10],
            'name'   => ['string', 'filter' => ['trim']],
        ];
    }

    /**
     * 场景
     * @return array
     */
    public function scenarios()
    {
        return [
            'room' => ['required' => ['roomId', 'name']],
        ];
    }

}
