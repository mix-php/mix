<?php

namespace mix\validator;

/**
 * InValidator类
 * @author 刘健 <coder.liu@qq.com>
 */
class InValidator extends BaseValidator
{

    // 允许的功能集合
    protected $_allowActions = ['range', 'strict'];

    // 范围验证
    protected function range($param)
    {
        $value  = $this->_attributeValue;
        $strict = empty($this->_settings['strict']) ? false : true;
        if (!in_array($value, $param, $strict)) {
            if (is_null($this->attributeMessage)) {
                $error = "{$this->attributeLabel}不在%s范围内.";
            } else {
                $error = $this->attributeMessage;
            }
            $this->errors[] = sprintf($error, implode('、', $param));
            return false;
        }
        return true;
    }

}
