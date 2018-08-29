<?php

namespace apps\httpd\models;

use mix\validators\Validator;

class UserForm extends Validator
{

    public $name;
    public $age;
    public $email;

    // 规则
    public function rules()
    {
        return [
            'name'  => ['string', 'maxLength' => 25, 'filter' => ['trim']],
            'age'   => ['integer', 'unsigned' => true, 'min' => 1, 'max' => 120],
            'email' => ['email'],
        ];
    }

    // 场景
    public function scenarios()
    {
        return [
            'create' => ['required' => ['name'], 'optional' => ['email', 'age']],
        ];
    }

    // 消息
    public function messages()
    {
        return [
            'name.required'  => '名称不能为空.',
            'name.maxLength' => '名称最多不能超过25个字符.',
            'age.integer'    => '年龄必须是数字.',
            'age.unsigned'   => '年龄不能为负数.',
            'age.min'        => '年龄不能小于1.',
            'age.max'        => '年龄不能大于120.',
            'email'          => '邮箱格式错误.',
        ];
    }

}
