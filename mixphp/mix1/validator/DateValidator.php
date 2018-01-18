<?php

namespace mix\validator;

/**
 * DateValidator类
 * @author 刘健 <coder.liu@qq.com>
 */
class DateValidator extends BaseValidator
{

    // 允许的功能集合
    protected $_allowActions = ['format'];

    // 格式验证
    protected function format($param)
    {
        $value = $this->_attributeValue;
        $date = date_create($value);
        if (!$date || $value != date_format($date, $param)) {
            if (is_null($this->attributeMessage)) {
                $error = "{$this->attributeLabel}不符合日期格式.";
            } else {
                $error = $this->attributeMessage;
            }
            $this->errors[] = sprintf($error, $param);
            return false;
        }
        return true;
    }

}
