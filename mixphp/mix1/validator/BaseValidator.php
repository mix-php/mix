<?php

namespace mix\validator;

/**
 * 基础验证器类
 * @author 刘健 <coder.liu@qq.com>
 */
class BaseValidator
{

    // 需要验证的功能
    public $actions;

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

    // 错误
    public $errors;

    // 属性值
    protected $_attributeValue;

    // 允许的功能集合
    protected $_allowActions;

    // 设置
    protected $_settings;

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
        $this->errors           = [];
        $this->attributeMessage = $this->getAttributeMessage();
        $this->attributeLabel   = $this->getAttributeLabel();
        $this->_attributeValue  = $this->getAttributeValue();
        // 必需验证 and 标量类型验证
        $this->scalar() and $this->required(array_shift($this->actions));
        // 加入类型验证
        if (in_array('type', $this->_allowActions)) {
            $this->actions = ['type' => null] + $this->actions;
        }
        // 验证
        if (!is_null($this->_attributeValue)) {
            // 预处理
            foreach ($this->actions as $action => $param) {
                if (!in_array($action, $this->_allowActions)) {
                    throw new \mix\exception\ModelException("属性`{$this->attribute}`的验证方法`{$action}`不存在");
                }
                // actions拆分
                if (!method_exists($this, $action)) {
                    $this->_settings[$action] = $param;
                    unset($this->actions[$action]);
                }
            }
            // 执行
            foreach ($this->actions as $action => $param) {
                $this->$action($param);
            }
        }
        return empty($this->errors);
    }

    // 标量类型验证
    protected function scalar()
    {
        $value = $this->_attributeValue;
        if (!is_null($value) && !is_scalar($value)) {
            // 增加错误消息
            if (is_null($this->attributeMessage)) {
                $error = "{$this->attributeLabel}不是标量类型.";
            } else {
                $error = $this->attributeMessage;
            }
            $this->errors[] = $error;
            // 将非标量值置空
            $this->_attributeValue = null;
            // 返回
            return false;
        }
        return true;
    }

    // 必需验证
    protected function required($param)
    {
        $value = $this->_attributeValue;
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
        $value = $this->_attributeValue;
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
        $value = $this->_attributeValue;
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
        $value = $this->_attributeValue;
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
        $value = $this->_attributeValue;
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
        $value = $this->_attributeValue;
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
        $value = $this->_attributeValue;
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
