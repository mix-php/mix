<?php

namespace Mix\Validate\Validator;

use Mix\Validate\Validate;

/**
 * Class PhoneValidator
 * @package Mix\Validate\Validator
 */
class PhoneValidator extends BaseValidator
{

    // 初始化选项
    protected $initOptions = ['phone'];

    // 启用的选项
    protected $enabledOptions = [];

    // 类型验证
    protected function phone()
    {
        $value = $this->attributeValue;
        if (!Validate::isPhone($value)) {
            // 设置错误消息
            $defaultMessage = "{$this->attribute}不符合手机号格式.";
            $this->setError(__FUNCTION__, $defaultMessage);
            // 返回
            return false;
        }
        return true;
    }

}
