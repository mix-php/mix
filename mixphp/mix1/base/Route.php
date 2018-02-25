<?php

namespace mix\base;

/**
 * Route组件
 * @author 刘健 <coder.liu@qq.com>
 */
class Route extends Component
{

    // 默认变量规则
    public $defaultPattern = '[\w-]+';

    // 路由变量规则
    public $patterns = [];

    // 路由规则
    public $rules = [];

    // URL后缀
    public $suffix = '';

    // 路由数据
    protected $data = [];

    // 默认路由规则
    protected $defaultRules = [
        // 首页
        ''                    => 'index/index',
        // 一级目录
        ':controller/:action' => ':controller/:action',
    ];

    // 初始化事件
    public function onInitialize()
    {
        parent::onInitialize();
        // 初始化
        $this->initialize();
    }

    // 初始化，生成路由数据，将路由规则转换为正则表达式，并提取路由参数名
    public function initialize()
    {
        $this->rules += $this->defaultRules;
        // url目录index处理
        foreach ($this->rules as $rule => $action) {
            if (strpos($rule, ':controller') !== false && strpos($rule, ':action') !== false) {
                $this->rules[dirname($rule)] = str_replace(':action', 'index', $action);
            }
        }
        // 转正则
        foreach ($this->rules as $rule => $action) {
            // method
            if ($blank = strpos($rule, ' ')) {
                $method = substr($rule, 0, $blank);
                $method = "(?:{$method}) ";
                $rule   = substr($rule, $blank + 1);
            } else {
                $method = '(?:CLI|GET|POST|PUT|PATCH|DELETE|HEAD|OPTIONS) ';
            }
            // path
            $fragment = explode('/', $rule);
            $names    = [];
            foreach ($fragment as $k => $v) {
                $prefix = substr($v, 0, 1);
                $fname  = substr($v, 1);
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

    // 匹配功能
    public function match($action)
    {
        // 清空旧数据
        $urlParams = [];
        // 去除URL后缀
        $action = str_replace($this->suffix, '', $action);
        // 匹配
        $result = [];
        foreach ($this->data as $rule => $value) {
            list($ruleAction, $ruleParams) = $value;
            if (preg_match($rule, $action, $matches)) {
                // 保存参数
                foreach ($ruleParams as $k => $v) {
                    $urlParams[$v] = $matches[$k + 1];
                }
                // 替换参数
                $fragment = explode('/', $ruleAction);
                foreach ($fragment as $k => $v) {
                    $prefix = substr($v, 0, 1);
                    $fname  = substr($v, 1);
                    if ($prefix == ':') {
                        if (isset($urlParams[$fname])) {
                            $fragment[$k] = $urlParams[$fname];
                        }
                    }
                }
                // url目录index处理
                if (isset($urlParams['controller']) && !isset($urlParams['action'])) {
                    $urlParams['action'] = 'index';
                }
                // 无controller,action参数路由处理
                if (!isset($urlParams['controller']) && !isset($urlParams['action'])) {
                    $tmp                     = $fragment;
                    $urlParams['action']     = array_pop($tmp);
                    $urlParams['controller'] = array_pop($tmp);
                }
                // 记录action与params，由于路由歧义，会存在多条路由规则都可匹配的情况
                $result[] = [implode('\\', $fragment), $urlParams];
            }
        }
        return $result;
    }

    // 蛇形命名转换为驼峰命名
    public static function snakeToCamel($name, $ucfirst = false)
    {
        $name = ucwords(str_replace(['_', '-'], ' ', $name));
        $name = str_replace(' ', '', lcfirst($name));
        return $ucfirst ? ucfirst($name) : $name;
    }

    // 驼峰命名转换为蛇形命名
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

    // 返回路径中的目录部分，正反斜杠linux兼容处理
    public static function dirname($path)
    {
        if (strpos($path, '\\') === false) {
            return dirname($path);
        }
        return str_replace('/', '\\', dirname(str_replace('\\', '/', $path)));
    }

    // 返回路径中的文件名部分，正反斜杠linux兼容处理
    public static function basename($path)
    {
        if (strpos($path, '\\') === false) {
            return basename($path);
        }
        return str_replace('/', '\\', basename(str_replace('\\', '/', $path)));
    }

}
