<?php

/**
 * 基础验证器类
 * @author 刘健 <code.liu@qq.com>
 */

namespace mix\validator;

class BaseValidator
{

    // 全部属性
    public $attributes;

    // 当前属性名称
    public $attribute;

    // 属性通知
    public $attributeMessage;

    // 属性标签
    public $attributeLabel;

    // 属性值
    protected $attributeValue;

    // 需要验证的功能
    public $actions;

    // 错误
    public $errors;

    // 允许的功能集合
    protected $allowActions = [];

    // 获取属性值
    private function getAttributeValue()
    {
        return isset($this->attributes[$this->attribute]) ? $this->attributes[$this->attribute] : null;
    }

    // 验证
    public function validate()
    {
        $this->errors = null;
        $this->attributeValue = $this->getAttributeValue();
        $this->required(array_shift($this->actions));
        $this->actions = ['type' => true] + $this->actions;
        if (!is_null($this->attributeValue)) {
            foreach ($this->actions as $action => $param) {
                if (!in_array($action, $this->allowActions)) {
                    throw new \mix\exception\RouteException("属性`{$this->attribute}`的验证方法`{$action}`不存在");
                }
                $this->$action($param);
            }
        }
        return is_null($this->errors);
    }

    // 必需验证
    protected function required($param)
    {
        $value = $this->attributeValue;
        if ($param && is_null($value)) {
            if (is_null($this->attributeMessage)) {
                $this->errors[] = "{$this->attributeLabel}不能为空.";
            } else {
                $this->errors[] = "{$this->attributeLabel}{$this->attributeMessage}";
            }
            return false;
        }
        return true;
    }

    // 无符号验证
    protected function unsigned($param)
    {
        $value = $this->attributeValue;
        if ($param && substr($value, 0, 1) == '-') {
            if (is_null($this->attributeMessage)) {
                $this->errors[] = "{$this->attributeLabel}不能为负数.";
            } else {
                $this->errors[] = "{$this->attributeLabel}{$this->attributeMessage}";
            }
            return false;
        }
        return true;
    }

}
