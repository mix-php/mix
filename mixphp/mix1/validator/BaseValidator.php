<?php

/**
 * 基础验证器类
 * @author 刘健 <coder.liu@qq.com>
 */

namespace mix\validator;

class BaseValidator
{

    // 全部属性
    public $attributes;

    // 当前属性名称
    public $attribute;

    // 全部消息
    public $attributeMessages;

    // 属性消息
    public $attributeMessage;

    // 全部标签
    public $attributeLabels;

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

    // 获取消息
    protected function getAttributeMessage()
    {
        if (!isset($this->attributeMessages[$this->attribute])) {
            return null;
        }
        return $this->attributeMessages[$this->attribute];
    }

    // 获取标签
    protected function getAttributeLabel()
    {
        return isset($this->attributeLabels[$this->attribute]) ? $this->attributeLabels[$this->attribute] : \mix\base\Route::snakeToCamel($this->attribute, true);
    }

    // 获取属性值
    protected function getAttributeValue()
    {
        if (!isset($this->attributes[$this->attribute])) {
            return null;
        }
        if ($this->attributes[$this->attribute] == '') {
            return null;
        }
        return $this->attributes[$this->attribute];
    }

    // 验证
    public function validate()
    {
        $this->errors           = null;
        $this->attributeValue   = $this->getAttributeValue();
        $this->attributeMessage = $this->getAttributeMessage();
        $this->attributeLabel   = $this->getAttributeLabel();
        $this->required(array_shift($this->actions));
        if (in_array('type', $this->allowActions)) {
            $this->actions = ['type' => null] + $this->actions;
        }
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
                $error = "{$this->attributeLabel}不能为空.";
            } else {
                $error = $this->attributeMessage;
            }
            $this->errors[] = $error;
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
                $error = "{$this->attributeLabel}不能为负数.";
            } else {
                $error = $this->attributeMessage;
            }
            $this->errors[] = $error;
            return false;
        }
        return true;
    }

    // 最小数值验证
    protected function min($param)
    {
        $value = $this->attributeValue;
        if (is_numeric($value) && $value < $param) {
            if (is_null($this->attributeMessage)) {
                $error = "{$this->attributeLabel}不能小于%s.";
            } else {
                $error = $this->attributeMessage;
            }
            $this->errors[] = sprintf($error, $param);
            return false;
        }
        return true;
    }

    // 最大数值验证
    protected function max($param)
    {
        $value = $this->attributeValue;
        if (is_numeric($value) && $value > $param) {
            if (is_null($this->attributeMessage)) {
                $error = "{$this->attributeLabel}不能大于%s.";
            } else {
                $error = $this->attributeMessage;
            }
            $this->errors[] = sprintf($error, $param);
            return false;
        }
        return true;
    }

    // 固定长度验证
    protected function length($param)
    {
        $value = $this->attributeValue;
        if (mb_strlen($value) != $param) {
            if (is_null($this->attributeMessage)) {
                $error = "{$this->attributeLabel}长度只能为%s位.";
            } else {
                $error = $this->attributeMessage;
            }
            $this->errors[] = sprintf($error, $param);
            return false;
        }
        return true;
    }

    // 最小长度验证
    protected function minLength($param)
    {
        $value = $this->attributeValue;
        if (mb_strlen($value) < $param) {
            if (is_null($this->attributeMessage)) {
                $error = "{$this->attributeLabel}长度不能小于%s位.";
            } else {
                $error = $this->attributeMessage;
            }
            $this->errors[] = sprintf($error, $param);
            return false;
        }
        return true;
    }

    // 最大长度验证
    protected function maxLength($param)
    {
        $value = $this->attributeValue;
        if (mb_strlen($value) > $param) {
            if (is_null($this->attributeMessage)) {
                $error = "{$this->attributeLabel}长度不能大于%s位.";
            } else {
                $error = $this->attributeMessage;
            }
            $this->errors[] = sprintf($error, $param);
            return false;
        }
        return true;
    }

}
