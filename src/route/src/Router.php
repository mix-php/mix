<?php

namespace Mix\Route;

use Mix\Bean\BeanInjector;
use Mix\Route\Exception\NotFoundException;

/**
 * Class Router
 * @package Mix\Route
 * @author liu,jian <coder.keda@gmail.com>
 */
class Router
{

    /**
     * 默认变量规则
     * @var string
     */
    public $defaultPattern = '[\w-]+';

    /**
     * 路由变量规则
     * @var array
     */
    public $patterns = [];

    /**
     * 全局中间件
     * @var array
     */
    public $middleware = [];

    /**
     * 路由规则
     * @var array
     */
    public $rules = [];

    /**
     * 转化后的路由规则
     * @var array
     */
    protected $materials = [];

    /**
     * Router constructor.
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        BeanInjector::inject($this, $config);
    }

    /**
     * 解析
     * 生成路由数据，将路由规则转换为正则表达式，并提取路由参数名
     */
    public function parse(): void
    {
        $rules           = $this->merge($this->rules, $this->middleware);
        $this->materials = $this->convert($rules);
    }

    /**
     * 合并中间件、分组
     * @param array $rules
     * @param array $middleware
     * @return array
     */
    protected function merge(array $rules, array $middleware): array
    {
        $data = [];
        foreach ($rules as $url => $rule) {
            $rule['middleware'] = $rule['middleware'] ?? [];
            if (isset($rule['rules'])) {
                // 分组处理
                foreach ($rule['rules'] as $gUrl => $gRule) {
                    $gUrl                = substr_replace($gUrl, $url . '/', strpos($gUrl, '/'), 1);
                    $gRule['middleware'] = $gRule['middleware'] ?? [];
                    $gRule['middleware'] = array_merge($middleware, $rule['middleware'], $gRule['middleware']);
                    $data[$gUrl]         = $gRule;
                }
            } else {
                $rule['middleware'] = array_merge($middleware, $rule['middleware']);
            }
            $data[$url] = $rule;
        }
        return $data;
    }

    /**
     * 转换正则
     * @param array $rules
     * @return array
     */
    protected function convert(array $rules): array
    {
        $materials = [];
        foreach ($rules as $rule => $route) {
            if ($blank = strpos($rule, ' ')) {
                $method = substr($rule, 0, $blank);
                $method = "(?:{$method}) ";
                $rule   = substr($rule, $blank + 1);
            } else {
                $method = '(?:GET|POST|PUT|PATCH|DELETE|HEAD|OPTIONS) ';
            }
            $fragment = explode('/', $rule);
            $var      = [];
            foreach ($fragment as $k => $v) {
                preg_match('/{([\w-]+)}/i', $v, $matches);
                if (empty($matches)) {
                    continue;
                }
                list($fname) = $matches;
                $fname = substr($fname, 1, -1);
                if (isset($this->patterns[$fname])) {
                    $fragment[$k] = str_replace('{' . $fname . '}', "({$this->patterns[$fname]})", $fragment[$k]);
                } else {
                    $fragment[$k] = str_replace('{' . $fname . '}', "({$this->defaultPattern})", $fragment[$k]);
                }
                $var[] = $fname;
            }
            $pattern     = '/^' . $method . implode('\/', $fragment) . '$/i';
            $materials[] = [$pattern, $route, $var];
        }
        return $materials;
    }

    /**
     * 匹配
     * @param string $method
     * @param string $pathinfo
     * @return Result
     * @throws \PhpDocReader\AnnotationException
     * @throws \ReflectionException
     */
    public function match(string $method, string $pathinfo): Result
    {
        // 由于路由歧义，会存在多条路由规则都可匹配的情况
        $result = [];
        foreach ($this->materials as $item) {
            list($pattern, $route, $var) = $item;
            if (preg_match($pattern, "{$method} {$pathinfo}", $matches)) {
                $params = [];
                // 提取路由查询参数
                foreach ($var as $k => $v) {
                    $params[$v] = $matches[$k + 1];
                }
                // 记录参数
                $result[] = [$route, $params];
            }
        }
        // 筛选有效的结果
        foreach ($result as $item) {
            list($route, $params) = $item;
            $callback = array_shift($route);
            if (is_callable($callback)) {
                // 返回
                return new Result($callback, $route['middleware'], $params);
            }
        }
        throw new NotFoundException('Not Found (#404)');
    }

}
