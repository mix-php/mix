<?php

namespace Mix\Validate\Validator;

use Mix\Validate\Validate;

/**
 * Class EmailValidator
 * @package Mix\Validate\Validator
 */
class EmailValidator extends BaseValidator
{

    // 初始化选项
    protected $initOptions = ['email'];

    // 启用的选项
    protected $enabledOptions = ['length', 'minLength', 'maxLength'];

    // 类型验证
    protected function email()
    {
        $value = $this->attributeValue;
        if (!Validate::isEmail($value)) {
            // 设置错误消息
            $defaultMessage = "{$this->attribute}不符合邮箱格式.";
            $this->setError(__FUNCTION__, $defaultMessage);
            // 返回
            return false;
        }
        return true;
    }

}
