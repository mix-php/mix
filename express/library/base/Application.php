<?php

/**
 * App类
 * @author 刘健 <code.liu@qq.com>
 */

namespace express\base;

class Application
{

    // 应用根路径
    public $basePath = '\\';
    // 控制器命名空间
    public $controllerNamespace = 'www\controller';
    // 注册树配置
    public $register = [];

    /**
     * 构造
     * @param array $config
     */
    public function __construct($config)
    {
        // 添加属性
        foreach ($config as $key => $value) {
            $this->$key = $value;
        }
        // 快捷引用
        \Express::$app = $this;
    }

    /**
     * 注册树实例化
     * @param  string $name
     */
    public function __get($name)
    {
        // 返回单例
        if (isset($this->$name)) {
            return $this->$name;
        }
        // 获取配置
        $list  = $this->register[$name];
        $class = $list['class'];
        // 实例化
        $object = new $class(['disableInit' => true]);
        // 属性导入
        foreach ($list as $key => $value) {
            // 跳过保留key
            if (in_array($key, ['class', 'singleton'])) {
                continue;
            }
            // 属性赋值
            if (is_array($value) && isset($value['class'])) {
                // 获取配置
                $subClass = $value['class'];
                // 实例化
                $subObject = new $subClass(['disableInit' => true]);
                // 属性导入
                foreach ($value as $k => $v) {
                    if (in_array($k, ['class'])) {
                        continue;
                    }
                    $subObject->$k = $v;
                }
                $object->$key = $subObject;
                // 执行初始化方法
                method_exists($subObject, 'init') and $subObject->init();
            } else {
                $object->$key = $value;
            }
        }
        // 执行初始化方法
        method_exists($object, 'init') and $object->init();
        // 返回新对象
        if (isset($list['singleton']) && $list['singleton'] == false) {
            return $object;
        }
        return $this->$name = $object;
    }

    /**
     * 执行功能并返回
     * @param  string $action
     * @param  array  $inout
     * @return mixed
     */
    public function runAction($method, $action, $inout = [])
    {
        $action = "{$method} {$action}";
        // 路由匹配
        list($action, $urlParams) = \Express::$app->route->match($action);
        // 执行功能
        if ($action) {
            // 路由参数导入请求类
            if (empty($inout['request'])) {
                \Express::$app->request->setRoute($urlParams);
            } else {
                $inout['request']->setRoute($urlParams);
            }
            // index处理
            if (isset($urlParams['controller']) && strpos($action, ':action') !== false) {
                $action = str_replace(':action', 'index', $action);
            }
            // 实例化控制器
            $action    = "{$this->controllerNamespace}\\{$action}";
            $classFull = dirname($action);
            $classPath = dirname($classFull);
            $className = \Express::$app->route->snakeToCamel(basename($classFull));
            $method    = \Express::$app->route->snakeToCamel(basename($action), true);
            $class     = "{$classPath}\\{$className}Controller";
            $method    = "action{$method}";
            try {
                $reflect = new \ReflectionClass($class);
            } catch (\ReflectionException $e) {
                throw new \express\exception\HttpException("URL不存在", 404);
            }
            $controller = $reflect->newInstanceArgs();
            // 判断方法是否存在
            if (method_exists($controller, $method)) {
                // 执行控制器的方法
                if (empty($inout)) {
                    return $controller->$method();
                } else {
                    return $controller->$method($inout['request'], $inout['response']);
                }
            }
        }
        throw new \express\exception\HttpException("URL不存在", 404);
    }

    /**
     * 获取配置路径
     * @return string
     */
    public function getConfigPath()
    {
        return $this->basePath . 'config' . DS;
    }

    /**
     * 获取运行路径
     * @return string
     */
    public function getRuntimePath()
    {
        return $this->basePath . 'runtime' . DS;
    }

    /**
     * 获取视图路径
     * @return string
     */
    public function getViewPath()
    {
        return $this->basePath . 'view' . DS;
    }

}
