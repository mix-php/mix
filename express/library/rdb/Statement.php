<?php

/**
 * Statement类
 * @author 刘健 <code.liu@qq.com>
 */

namespace sys\rdb;

use sys\Config;

class Statement
{

    // PDOStatement对象
    private $PDOStatement;

    // 是否为驼峰命名
    private $isCamelCase = false;

    public function __construct($PDOStatement)
    {
        $this->PDOStatement = $PDOStatement;
        $this->isCamelCase  = Config::get('pdo.case_camel');
    }

    // 调用方法
    public function __call($method, $args = [])
    {
        $result = call_user_func_array([$this->PDOStatement, $method], $args);
        // 驼峰转换
        if ($this->isCamelCase && in_array($method, ['fetch', 'fetchObject', 'fetchAll'])) {
            if (is_array($result) && isset($result[0]) && !is_scalar($result[0])) {
                // 二维
                $result = self::convertTwodimensional($result);
            } else {
                // 一维
                $result = self::convertLinear($result);
            }
        }
        return $result;
    }

    // 获取属性
    public function __get($name)
    {
        return $this->PDOStatement->$name;
    }

    // 转换一维结果集
    private static function convertLinear($result)
    {
        if (empty($result)) {
            return $result;
        }
        // 重构数据
        $isArray   = is_array($result);
        $newResult = [];
        foreach ($result as $key => $value) {
            $newResult[\sys\App::snakeToCamel($key)] = $value;
        }
        return $isArray ? $newResult : (object) $newResult;
    }

    // 转换二维结果集
    private static function convertTwodimensional($result)
    {
        if (empty($result)) {
            return $result;
        }
        // 转换列名
        $row     = $result[0];
        $isArray = is_array($row);
        $column  = [];
        foreach ($row as $key => $value) {
            $column[$key] = \sys\App::snakeToCamel($key);
        }
        // 重构数据
        $newResult = [];
        foreach ($result as $key => $value) {
            $tmp = [];
            foreach ($value as $k => $v) {
                $tmp[$column[$k]] = $v;
            }
            $newResult[] = $isArray ? $tmp : (object) $tmp;
        }
        return $newResult;
    }

}
