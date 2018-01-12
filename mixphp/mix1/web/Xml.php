<?php

namespace mix\web;

use mix\base\BaseObject;

/**
 * Xml类
 * @author 刘健 <coder.liu@qq.com>
 */
class Xml extends BaseObject
{

    // 输出
    public static function encode($array)
    {
        // 输出
        $xmlString = '<?xml version="1.0" encoding="utf-8"?>';
        $xmlString .= '<xml>';
        foreach ($array as $key => $value) {
            $xmlString .= self::addChild($key, $value);
        }
        $xmlString .= '</xml>';
        return $xmlString;
    }

    // 创建XML子项
    protected static function addChild($key, $value)
    {
        $xmlString = '';
        if (is_array($value)) {
            $xmlString .= (is_numeric($key) ? "<item id='{$key}'>" : "<{$key}>");
            foreach ($value as $k => $v) {
                $xmlString .= self::addChild($k, $v);
            }
            $xmlString .= (is_numeric($key) ? '</item>' : "</{$key}>");
        } else {
            $xmlString = "<{$key}>" . $value . "</{$key}>";
        }
        return $xmlString;
    }

}
