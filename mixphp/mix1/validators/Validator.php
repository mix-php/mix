<?php

/**
 * Validator类
 * @author 刘健 <code.liu@qq.com>
 */

namespace mix\validators;

class Validator
{

    // 无符号验证
    public function unsigned($value)
    {
        if (strlen($value) == 0 || substr($value, 0, 1) !== '-') {
            return false;
        }
        return true;
    }

}
