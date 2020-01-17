<?php

namespace Mix\Validate\Validator;

use Mix\Validate\Validate;

/**
 * AlphaNumericValidator类
 * @author liu,jian <coder.keda@gmail.com>
 */
class AlphaNumericValidator extends BaseValidator
{

    // 初始化选项
    protected $_initOptions = ['alphaNumeric'];

    // 启用的选项
    protected $_enabledOptions = ['length', 'minLength', 'maxLength'];

    // 类型验证
    protected function alphaNumeric()
    {
        $value = $this->attributeValue;
        if (!Validate::isAlphaNumeric($value)) {
            // 设置错误消息
            $defaultMessage = "{$this->attribute}只能为字母和数字.";
            $this->setError(__FUNCTION__, $defaultMessage);
            // 返回
            return false;
        }
        return true;
    }

}
