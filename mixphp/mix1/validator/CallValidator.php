<?php

namespace mix\validator;

/**
 * CallValidator类
 * @author 刘健 <coder.liu@qq.com>
 */
class CallValidator extends BaseValidator
{

    // 允许的功能集合
    protected $_allowActions = ['callback'];

    // 回调验证
    protected function callback($param)
    {
        if (!call_user_func_array($param, [$this->attributes[$this->attribute]])) {
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
