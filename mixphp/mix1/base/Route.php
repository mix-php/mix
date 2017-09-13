<?php

/**
 * Route类
 * @author 刘健 <code.liu@qq.com>
 */

namespace mix\base;

class Route extends Object
{

    // 默认变量规则
    public $defaultPattern = '[\w-]+';
    // 路由变量规则
    public $patterns = [];
    // 路由规则
    public $rules = [];
    // 路由数据
    private $data = [];
    // 默认路由规则
    private $defaultRules = [
        // 首页
        ''                    => 'index/index',
        // 一级目录
        ':controller/:action' => ':controller/:action',
    ];

    /**
     * 初始化
     * 生成路由数据，将路由规则转换为正则表达式，并提取路由参数名
     */
    public function init()
    {
        $this->rules += $this->defaultRules;
        // index处理
        foreach ($this->rules as $rule => $action) {
            if (strpos($rule, ':controller') !== false && strpos($rule, ':action') !== false) {
                $this->rules[dirname($rule)] = $action;
            }
        }
        // 转正则
        foreach ($this->rules as $rule => $action) {
            // method
            if ($blank = strpos($rule, ' ')) {
                $method = substr($rule, 0, $blank);
                $method = "(?:{$method}) ";
                $rule = substr($rule, $blank + 1);
            } else {
                $method = '(?:POST|GET|CLI)* ';
            }
            // path
            $fragment = explode('/', $rule);
            $names = [];
            foreach ($fragment as $k => $v) {
                $prefix = substr($v, 0, 1);
                $fname = substr($v, 1);
                if ($prefix == ':') {
                    if (isset($this->patterns[$fname])) {
                        $fragment[$k] = '(' . $this->patterns[$fname] . ')';
                    } else {
                        $fragment[$k] = '(' . $this->defaultPattern . ')';
                    }
                    $names[] = $fname;
                }
            }
            $this->data['/^' . $method . implode('\/', $fragment) . '\/*$/i'] = [$action, $names];
        }
    }

    /**
     * 匹配功能
     * @param  string $name
     * @return false or string
     */
    public function match($name)
    {
        // 清空旧数据
        $urlParams = [];
        // 匹配
        foreach ($this->data as $rule => $value) {
            list($action, $names) = $value;
            if (preg_match($rule, $name, $matches)) {
                // 保存参数
                foreach ($names as $k => $v) {
                    $urlParams[$v] = $matches[$k + 1];
                }
                // 替换参数
                $fragment = explode('/', $action);
                foreach ($fragment as $k => $v) {
                    $prefix = substr($v, 0, 1);
                    $fname = substr($v, 1);
                    if ($prefix == ':') {
                        if (isset($urlParams[$fname])) {
                            $fragment[$k] = $urlParams[$fname];
                        }
                    }
                }
                // 返回action
                return [implode('\\', $fragment), $urlParams];
            }
        }
        return false;
    }

    /**
     * 蛇形命名转换为驼峰命名
     * @param  string $name
     * @param  boolean $ucfirst
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
     * @param  string $name
     * @return string
     */
    public static function camelToSnake($name)
    {
        $name = preg_replace_callback('/([A-Z]{1})/', function ($matches) {
            return '_' . strtolower($matches[0]);
        }, $name);
        if (substr($name, 0, 1) == '_') {
            return substr($name, 1);
        }
        return $name;
    }

    /**
     * 返回路径中的目录部分
     * 正反斜杠linux兼容处理
     */
    public static function dirname($path)
    {
        if (strpos($path, '\\') === false) {
            return dirname($path);
        }
        return str_replace('/', '\\', dirname(str_replace('\\', '/', $path)));
    }

    /**
     * 返回路径中的文件名部分
     * 正反斜杠linux兼容处理
     */
    public static function basename($path)
    {
        if (strpos($path, '\\') === false) {
            return basename($path);
        }
        return str_replace('/', '\\', basename(str_replace('\\', '/', $path)));
    }

}
