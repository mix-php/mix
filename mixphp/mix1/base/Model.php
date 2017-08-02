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

    // 验证器类路径
    private $_validatorsClass = [
        'integer'      => 'mix\validators\IntegerValidator',
        'double'       => 'mix\validators\DoubleValidator',
        'alpha'        => 'mix\validators\AlphaValidator',
        'alphaNumeric' => 'mix\validators\AlphaNumericValidator',
        'string'       => 'mix\validators\StringValidator',
        'in'           => 'mix\validators\InValidator',
        'date'         => 'mix\validators\DateValidator',
        'email'        => 'mix\validators\EmailValidator',
        'phone'        => 'mix\validators\PhoneValidator',
        'url'          => 'mix\validators\UrlValidator',
        'compare'      => 'mix\validators\CompareValidator',
        'match'        => 'mix\validators\MatchValidator',
        'call'         => 'mix\validators\CallValidator',
    ];

    // 规则
    public function rules()
    {
        return [];
    }

    // 场景
    public function scenarios()
    {
        return [];
    }

    // 属性标签
    public function attributeMessages()
    {
        return [];
    }

    // 属性标签
    public function attributeLabels()
    {
        return [];
    }

    // 设置当前场景
    public function setScenario($scenario)
    {
        $scenarios = $this->scenarios();
        if (!array_key_exists($scenario, $scenarios)) {
            throw new \mix\exception\ModelException("场景不存在：{$scenario}");
        }
        $this->_scenario = $scenario;
    }

    // 验证
    public function validate()
    {
        if (!isset($this->_scenario)) {
            throw new \mix\exception\ModelException("场景未设置");
        }
        $rules = $this->rules();

    }

}
