<?php

/**
 * Model类
 * @author 刘健 <code.liu@qq.com>
 */

namespace mix\base;

class Model extends Object
{

    // 全部属性
    public $attributes;
    // 错误
    public $errors;
    // 当前场景
    private $_scenario;

    // 规则
    public function rules()
    {
        return [
            ['a', 'natural'],
            ['b', 'integer'],
            ['c', 'numeric'],
            ['d', 'alpha'],
            ['e', 'alphaNumeric'],
            ['f', 'string', 'mix' => 1, 'max' => 15],
            ['g', 'in', 'range' => ['A', 'B']],
            ['h', 'match', 'pattern' => '/^[\w]{1,30}$/'],
        ];
    }

    // 场景
    public function scenarios()
    {
        return [
            'create' => ['a', 'b', 'c', 'd', 'nullable' => ['e']],
        ];
    }

    // 属性标签
    public function attributeLabels()
    {
        return [
            'a' => '参数A',
            'b' => '参数B',
            'c' => '参数C',
            'd' => '参数D',
            'e' => '参数E',
        ];
    }

    // 设置当前场景
    public function setScenario($scenario)
    {
        $this->_scenario = $scenario;
    }

    // 验证
    public function validate()
    {
        $rules = $this->rules();

    }

}
