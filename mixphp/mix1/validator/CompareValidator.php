<?php

/**
 * CompareValidator类
 * @author 刘健 <coder.liu@qq.com>
 */

namespace mix\validator;

class CompareValidator extends BaseValidator
{

    // 允许的功能集合
    protected $allowActions = ['compareAttribute'];

    // 比较属性
    protected function compareAttribute($param)
    {
        $value = $this->attributeValue;
        if (!isset($this->attributes[$param]) || $value != $this->attributes[$param]) {
            if (is_null($this->attributeMessage)) {
                $error = "{$this->attributeLabel}不等于%s.";
            } else {
                $error = $this->attributeMessage;
            }
            $paramLabel = isset($this->attributeLabels[$param]) ? $this->attributeLabels[$param] : ucfirst($param);
            $this->errors[] = sprintf($error, $paramLabel);
            return false;
        }
        return true;
    }

}
