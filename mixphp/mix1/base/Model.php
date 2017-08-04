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
        'integer'      => 'mix\validator\IntegerValidator',
        'double'       => 'mix\validator\DoubleValidator',
        'alpha'        => 'mix\validator\AlphaValidator',
        'alphaNumeric' => 'mix\validator\AlphaNumericValidator',
        'string'       => 'mix\validator\StringValidator',
        'in'           => 'mix\validator\InValidator',
        'date'         => 'mix\validator\DateValidator',
        'email'        => 'mix\validator\EmailValidator',
        'phone'        => 'mix\validator\PhoneValidator',
        'url'          => 'mix\validator\UrlValidator',
        'compare'      => 'mix\validator\CompareValidator',
        'match'        => 'mix\validator\MatchValidator',
        'call'         => 'mix\validator\CallValidator',
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
            throw new \mix\exception\ModelException("未设置场景");
        }
        $rules = $this->rules();
        $action = new \mix\validator\ActionValidator();
        foreach($this->_validatorsClass as $class){

        }
    }

}
