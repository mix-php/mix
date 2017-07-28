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
        ''                    => 'site/index',
        // 一级目录
        ':controller/:action' => ':controller/:action',
    ];

    /**
     * 初始化
     * 生成路由数据，将路由规则转换为正则表达式，并提取路由参数名
     */
    public function init()
    {
        $rules = $this->rules + $this->defaultRules;
        // index处理
        foreach ($rules as $rule => $action) {
            if (strpos($rule, ':controller') !== false && strpos($rule, ':action') !== false) {
                $rules[dirname($rule)] = $action;
            }
        }
        // 转正则
        foreach ($rules as $rule => $action) {
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
    public function snakeToCamel($name, $ucfirst = false)
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
    public function camelToSnake($name)
    {
        $name = preg_replace_callback('/([A-Z]{1})/', function ($matches) {
            return '_' . strtolower($matches[0]);
        }, $name);
        if (substr($name, 0, 1) == '_') {
            return substr($name, 1);
        }
        return $name;
    }

}
