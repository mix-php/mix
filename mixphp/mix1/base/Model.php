<?php

namespace mix\base;

/**
 * Model基类
 * @author 刘健 <coder.liu@qq.com>
 */
class Model extends BaseObject
{

    // 全部属性
    public $attributes;

    // 错误
    public $errors;

    // 当前场景
    private $_scenario;

    // 验证器类路径
    private $_validators = [
        'integer'      => '\mix\validator\IntegerValidator',
        'double'       => '\mix\validator\DoubleValidator',
        'alpha'        => '\mix\validator\AlphaValidator',
        'alphaNumeric' => '\mix\validator\AlphaNumericValidator',
        'string'       => '\mix\validator\StringValidator',
        'in'           => '\mix\validator\InValidator',
        'date'         => '\mix\validator\DateValidator',
        'email'        => '\mix\validator\EmailValidator',
        'phone'        => '\mix\validator\PhoneValidator',
        'url'          => '\mix\validator\UrlValidator',
        'compare'      => '\mix\validator\CompareValidator',
        'match'        => '\mix\validator\MatchValidator',
        'call'         => '\mix\validator\CallValidator',
        'file'         => '\mix\validator\FileValidator',
        'image'        => '\mix\validator\ImageValidator',
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

    // 属性消息
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
        if (!isset($scenarios[$scenario])) {
            throw new \mix\exception\ModelException("场景不存在：{$scenario}");
        }
        if (!isset($scenarios[$scenario]['required'])) {
            throw new \mix\exception\ModelException("场景`{$scenario}`缺少`required`键名");
        }
        if (!isset($scenarios[$scenario]['optional'])) {
            $scenarios[$scenario]['optional'] = [];
        }
        $this->_scenario = $scenarios[$scenario];
    }

    // 验证
    public function validate()
    {
        if (!isset($this->_scenario)) {
            throw new \mix\exception\ModelException("场景未设置");
        }
        $this->errors = [];
        $scenario = $this->_scenario;
        $scenarioAttributes = array_merge($scenario['required'], $scenario['optional']);
        $rules = $this->rules();
        $attributeMessages = $this->attributeMessages();
        $attributeLabels = $this->attributeLabels();
        // 验证器验证
        foreach ($rules as $rule) {
            $attribute = array_shift($rule);
            if (!in_array($attribute, $scenarioAttributes)) {
                continue;
            }
            $validatorType = array_shift($rule);
            if (!isset($this->_validators[$validatorType])) {
                throw new \mix\exception\ModelException("属性`{$attribute}`的验证类型`{$validatorType}`不存在");
            }
            $validatorClass = $this->_validators[$validatorType];
            $validator = new $validatorClass();
            // 必需验证
            if (in_array($attribute, $scenario['required'])) {
                array_unshift($rule, true);
            } else {
                array_unshift($rule, false);
            }
            // 传入属性
            $validator->actions = $rule;
            $validator->attributes = &$this->attributes;
            $validator->attributeMessages = $attributeMessages;
            $validator->attributeLabels = $attributeLabels;
            $validator->attribute = $attribute;
            // 验证
            if (!$validator->validate()) {
                // 累计汇总错误信息
                if (!isset($this->errors[$attribute])) {
                    $this->errors[$attribute] = $validator->errors;
                } else {
                    $this->errors[$attribute] = array_merge($this->errors[$attribute], $validator->errors);
                }
            } else if ($validator instanceof \mix\validator\FileValidator) {
                $validator->createInstance();
            }
        }
        // 验证通过后属性赋值
        foreach ($scenarioAttributes as $scenarioAttribute) {
            if (!isset($this->errors[$scenarioAttribute]) && isset($this->attributes[$scenarioAttribute])) {
                $this->$scenarioAttribute = $this->attributes[$scenarioAttribute];
            } else {
                $this->$scenarioAttribute = null;
            }
        }
        return empty($this->errors);
    }

}
