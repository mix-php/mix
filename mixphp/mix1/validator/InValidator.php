<?php

/**
 * InValidator类
 * @author 刘健 <coder.liu@qq.com>
 */

namespace mix\validator;

class InValidator extends BaseValidator
{

    // 允许的功能集合
    protected $allowActions = ['range'];

    // 范围验证
    protected function range($param)
    {
        $value = $this->attributeValue;
        if (!in_array($value, $param)) {
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
