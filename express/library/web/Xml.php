<?php

/**
 * Xml类
 * @author 刘健 <code.liu@qq.com>
 */

namespace sys\web;

class Xml
{

    // 原始数据
    private $array;

    public function __construct($array)
    {
        $this->array = $array;
    }

    // 创建实例
    public static function create($array)
    {
        return new self($array);
    }

    // 输出
    public function output()
    {
        // 设置Content-Type
        header('Content-type:text/xml;charset=utf-8');
        // 输出
        $xmlString = '<?xml version="1.0" encoding="utf-8"?>';
        $xmlString .= '<xml>';
        foreach ($this->array as $key => $value) {
            $xmlString .= self::addChild($key, $value);
        }
        $xmlString .= '</xml>';
        echo $xmlString;
        exit;
    }

    // 创建XML子项
    private static function addChild($key, $value)
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
