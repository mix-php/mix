<?php

namespace Mix\Validate\Validator;

/**
 * CompareValidator类
 * @author liu,jian <coder.keda@gmail.com>
 */
class CompareValidator extends BaseValidator
{

    // 启用的选项
    protected $_enabledOptions = ['compareAttribute'];

    // 比较属性
    protected function compareAttribute($param)
    {
        $value = $this->attributeValue;
        if (!isset($this->attributes[$param]) || $value != $this->attributes[$param]) {
            // 设置错误消息
            $defaultMessage = "{$this->attribute}不等于{$param}.";
            $this->setError(__FUNCTION__, $defaultMessage);
            // 返回
            return false;
        }
        return true;
    }

}
