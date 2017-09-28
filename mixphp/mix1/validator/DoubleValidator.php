<?php

/**
 * DoubleValidator类
 * @author 刘健 <coder.liu@qq.com>
 */

namespace mix\validator;

class DoubleValidator extends BaseValidator
{

    // 允许的功能集合
    protected $allowActions = ['type', 'unsigned', 'min', 'max', 'length', 'minLength', 'maxLength'];

    // 类型验证
    protected function type()
    {
        $value = $this->attributeValue;
        if (!preg_match('/^[-]{0,1}[0-9]+[.][0-9]+$|^[-]{0,1}[0-9]$/i', $value)) {
            if (is_null($this->attributeMessage)) {
                $error = "{$this->attributeLabel}只能为小数.";
            } else {
                $error = $this->attributeMessage;
            }
            $this->errors[] = $error;
            return false;
        }
        return true;
    }

}
