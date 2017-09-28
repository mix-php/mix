<?php

/**
 * MatchValidator类
 * @author 刘健 <coder.liu@qq.com>
 */

namespace mix\validator;

class MatchValidator extends BaseValidator
{

    // 允许的功能集合
    protected $allowActions = ['pattern'];

    // 正则验证
    protected function pattern($param)
    {
        $value = $this->attributeValue;
        if (!preg_match($param, $value)) {
            if (is_null($this->attributeMessage)) {
                $error = "{$this->attributeLabel}是无效的值.";
            } else {
                $error = $this->attributeMessage;
            }
            $this->errors[] = sprintf($error, $param);
            return false;
        }
        return true;
    }

}
