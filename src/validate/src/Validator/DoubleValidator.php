<?php

namespace Mix\Validate\Validator;

use Mix\Validate\Validate;

/**
 * Class DoubleValidator
 * @package Mix\Validate\Validator
 */
class DoubleValidator extends BaseValidator
{

    // 初始化选项
    protected $initOptions = ['double'];

    // 启用的选项
    protected $enabledOptions = ['unsigned', 'min', 'max', 'length', 'minLength', 'maxLength'];

    // 类型验证
    protected function double()
    {
        $value = $this->attributeValue;
        if (!Validate::isDouble($value)) {
            // 设置错误消息
            $defaultMessage = "{$this->attribute}只能为小数.";
            $this->setError(__FUNCTION__, $defaultMessage);
            // 返回
            return false;
        }
        return true;
    }

}
