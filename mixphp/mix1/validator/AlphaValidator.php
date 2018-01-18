<?php

namespace mix\validator;

/**
 * AlphaValidator类
 * @author 刘健 <coder.liu@qq.com>
 */
class AlphaValidator extends BaseValidator
{

    // 允许的功能集合
    protected $_allowActions = ['type', 'length', 'minLength', 'maxLength'];

    // 类型验证
    protected function type()
    {
        $value = $this->_attributeValue;
        if (!preg_match('/^[a-zA-Z]+$/i', $value)) {
            if (is_null($this->attributeMessage)) {
                $error = "{$this->attributeLabel}只能为字母.";
            } else {
                $error = $this->attributeMessage;
            }
            $this->errors[] = $error;
            return false;
        }
        return true;
    }

}
