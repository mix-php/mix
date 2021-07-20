<?php

namespace Mix\Validate\Validator;

use Mix\Validate\Validate;

/**
 * Class AlphaValidator
 * @package Mix\Validate\Validator
 */
class AlphaValidator extends BaseValidator
{

    // 初始化选项
    protected $initOptions = ['alpha'];

    // 启用的选项
    protected $enabledOptions = ['length', 'minLength', 'maxLength'];

    // 类型验证
    protected function alpha()
    {
        $value = $this->attributeValue;
        if (!Validate::isAlpha($value)) {
            // 设置错误消息
            $defaultMessage = "{$this->attribute}只能为字母.";
            $this->setError(__FUNCTION__, $defaultMessage);
            // 返回
            return false;
        }
        return true;
    }

}
