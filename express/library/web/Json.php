<?php

/**
 * View类
 * @author 刘健 <code.liu@qq.com>
 */

namespace sys\web;

use sys\Config;

class Json
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
        // 过滤null
        if (Config::get('main.json.null_to_string')) {
            $this->array = self::filterNull($this->array);
        }
        // 设置Content-Type
        header('Content-Type:application/json;charset=utf-8');
        // 不转义中文、斜杠
        echo json_encode($this->array, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    // 过滤null
    public static function filterNull($array)
    {
        if (is_array($array)) {
            return array_map([__CLASS__, __METHOD__], $array);
        }
        return is_null($array) ? '' : $array;
    }

}
