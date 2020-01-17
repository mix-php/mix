<?php

namespace Mix\Helper;

/**
 * NameHelper类
 * @author liu,jian <coder.keda@gmail.com>
 */
class NameHelper
{

    /**
     * 蛇形命名转换为驼峰命名
     * @param $name
     * @param bool $ucfirst
     * @return string
     */
    public static function snakeToCamel($name, $ucfirst = false)
    {
        $name = ucwords(str_replace(['_', '-'], ' ', $name));
        $name = str_replace(' ', '', lcfirst($name));
        return $ucfirst ? ucfirst($name) : $name;
    }

    /**
     * 驼峰命名转换为蛇形命名
     * @param $name
     * @param string $separator
     * @return string
     */
    public static function camelToSnake($name, $separator = '_')
    {
        $name = preg_replace_callback('/([A-Z]{1})/', function ($matches) use ($separator) {
            return $separator . strtolower($matches[0]);
        }, $name);
        if (substr($name, 0, 1) == $separator) {
            return substr($name, 1);
        }
        return $name;
    }

}
