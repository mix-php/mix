<?php

/**
 * UrlValidator类
 * @author 刘健 <coder.liu@qq.com>
 */

namespace mix\validator;

class UrlValidator extends BaseValidator
{

    // 允许的功能集合
    protected $allowActions = ['type', 'length', 'minLength', 'maxLength'];

    // 类型验证
    protected function type()
    {
        $value = $this->attributeValue;
        if (!preg_match('/^[a-z]+:\/\/[\S]+$/i', $value)) {
            if (is_null($this->attributeMessage)) {
                $error = "{$this->attributeLabel}不符合网址格式.";
            } else {
                $error = $this->attributeMessage;
            }
            $this->errors[] = $error;
            return false;
        }
        return true;
    }

}
