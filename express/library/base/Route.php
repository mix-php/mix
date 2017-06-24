<?php

/**
 * Route类
 * @author 刘健 <code.liu@qq.com>
 */

namespace express\base;

class Route
{

    // 控制器命名空间
    public $controllerNamespace = 'www\controller';
    // 默认变量规则
    public $defaultPattern = '[\w-]+';
    // 路由变量规则
    public $patterns = [];
    // 初始路由规则
    private $defaultRules = [
        // 首页
        ''                    => 'site/index',
        // 一级目录
        ':controller/:action' => ':controller/:action',
    ];
    // 路由规则
    public $rules = [];
    // 路由数据
    private $data = [];

    /**
     * 初始化
     * 生成路由数据，将路由规则转换为正则表达式，并提取路由参数名
     */
    public function init()
    {
        $rules = $this->defaultRules + $this->rules;
        // index处理
        foreach ($rules as $rule => $action) {
            if (strpos($rule, ':action') !== false) {
                $rules[dirname($rule)] = $action;
            }
        }
        // 转正则
        foreach ($rules as $rule => $action) {
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
            $this->data['/^' . implode('\/', $fragment) . '\/*$/i'] = [$action, $names];
        }
    }

    /**
     * 执行功能
     * @param  string $name
     */
    public function runAction($name, $requestParams = [])
    {
        list($action, $urlParams) = $this->matchAction($name);
        // 路由参数导入请求类
        \Express::$app->request->setRoute($urlParams);
        // index处理
        if (isset($urlParams['controller']) && strpos($action, ':action') !== false) {
            $action = str_replace(':action', 'index', $action);
        }
        // 执行
        if ($action) {
            // 实例化控制器
            $action    = "{$this->controllerNamespace}\\{$action}";
            $class     = dirname($action);
            $classPath = dirname($class);
            $className = self::snakeToCamel(basename($class));
            $method    = self::snakeToCamel(basename($action), true);
            $class     = "{$classPath}\\{$className}Controller";
            $method    = "action{$method}";
            try {
                $reflect = new \ReflectionClass($class);
            } catch (\ReflectionException $e) {
                throw new \express\exception\RouteException('控制器未找到', $class);
            }
            $controller = $reflect->newInstanceArgs();
            // 判断方法是否存在
            if (method_exists($controller, $method)) {
                // 执行控制器的方法
                return $controller->$method($requestParams + ['route' => $urlParams]);
            }
        }
        throw new \express\exception\HttpException(404, 'URL不存在');
    }

    /**
     * 匹配功能
     * @param  string $name
     * @return false or string
     */
    public function matchAction($name)
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
                    $fname  = substr($v, 1);
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
     * 将蛇形命名转换为驼峰命名
     * @param  string  $name
     * @param  boolean $ucfirst
     * @return string
     */
    public static function snakeToCamel($name, $ucfirst = false)
    {
        $name = ucwords(str_replace(['_', '-'], ' ', $name));
        $name = str_replace(' ', '', lcfirst($name));
        return $ucfirst ? ucfirst($name) : $name;
    }

}
