<?php

namespace Mix\Validate;

/**
 * Class Validate
 * @package Mix\Validate
 */
class Validate
{

    /**
     * 验证是否为字母与数字
     * @param $value
     * @return bool
     */
    public static function isAlphaNumeric($value)
    {
        return preg_match('/^[a-zA-Z0-9]+$/i', $value) ? true : false;
    }

    /**
     * 验证是否为字母
     * @param $value
     * @return bool
     */
    public static function isAlpha($value)
    {
        return preg_match('/^[a-zA-Z]+$/i', $value) ? true : false;
    }

    /**
     * 验证是否为日期
     * @param $value
     * @param $format
     * @return bool
     */
    public static function isDate($value, $format)
    {
        $date = date_create($value);
        if (!$date || $value != date_format($date, $format)) {
            return false;
        }
        return true;
    }

    /**
     * 验证是否为浮点数
     * @param $value
     * @return bool
     */
    public static function isDouble($value)
    {
        return preg_match('/^[-]{0,1}[0-9]+[.][0-9]+$|^[-]{0,1}[0-9]$/i', $value) ? true : false;
    }

    /**
     * 验证是否为邮箱
     * @param $value
     * @return bool
     */
    public static function isEmail($value)
    {
        return preg_match('/^[\.a-zA-Z0-9_-]+@[a-zA-Z0-9_-]+(\.[a-zA-Z0-9_-]+)+$/i', $value) ? true : false;
    }

    /**
     * 验证是否为整数
     * @param $value
     * @return bool
     */
    public static function isInteger($value)
    {
        return preg_match('/^[-]{0,1}[0-9]+$/i', $value) ? true : false;
    }

    /**
     * 验证是否在某个范围
     * @param $value
     * @param $range
     * @param bool $strict
     * @return bool
     */
    public static function in($value, $range, $strict = false)
    {
        return in_array($value, $range, $strict) ? true : false;
    }

    /**
     * 正则验证
     * @param $value
     * @param $pattern
     * @return bool
     */
    public static function match($value, $pattern)
    {
        return preg_match($pattern, $value) ? true : false;
    }

    /**
     * 验证是否为手机
     * @param $value
     * @return bool
     */
    public static function isPhone($value)
    {
        return preg_match('/^1[3456789]\d{9}$/i', $value) ? true : false;
    }

    /**
     * 验证是否为网址
     * @param $value
     * @return bool
     */
    public static function isUrl($value)
    {
        return preg_match('/^[a-z]+:\/\/[\S]+$/i', $value) ? true : false;
    }

}
