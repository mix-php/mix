<?php

/**
 * View类
 * @author 刘健 <code.liu@qq.com>
 */

namespace sys\web;

use sys\Config;

class Jsonp extends json
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
            $this->array = parent::filterNull($this->array);
        }
        // 设置Content-Type
        header('Content-Type:application/json;charset=utf-8');
        // 不转义中文、斜杠
        $json = json_encode($this->array, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $callback = Config::get('main.json.jsonp_callback');
        echo $callback . '(' . $json . ')';
        exit;
    }

}
