<?php

namespace mix\base;

use mix\base\Component;

/**
 * App类
 * @author 刘健 <coder.liu@qq.com>
 *
 * @property \mix\base\Route $route
 * @property \mix\web\Request|\mix\swoole\Request|\mix\console\Request $request
 * @property \mix\web\Response|\mix\swoole\Response|\mix\console\Response $response
 * @property \mix\web\Error $error
 * @property \mix\base\Log $log
 * @property \mix\web\Session $session
 * @property \mix\web\Cookie $cookie
 * @property \mix\rdb\Pdo $rdb
 * @property \mix\nosql\Redis $redis
 * @property \mix\base\Config $config
 */
class Application
{

    // 应用根路径
    public $basePath = '';

    // 控制器命名空间
    public $controllerNamespace = 'index\controller';

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
        \Mix::setApp($this);
    }

    /**
     * 组件实例化
     * @param  string $name
     */
    public function __get($name)
    {
        // 返回单例
        if (isset($this->$name)) {
            // 触发请求开始事件
            if ($this->$name->getStatus() == Component::STATUS_READY) {
                $this->$name->onRequestStart();
                $this->$name->setStatus(Component::STATUS_RUNNING);
            }
            // 返回对象
            return $this->$name;
        }
        // 未注册
        if (!isset($this->register[$name])) {
            throw new \mix\exception\ComponentException("组件不存在：{$name}");
        }
        // 获取配置
        $list  = $this->register[$name];
        $class = $list['class'];
        // 实例化
        $object = new $class();
        // 组件效验
        if (!($object instanceof Component)) {
            throw new \mix\exception\ComponentException("不是组件类型：{$class}");
        }
        // 属性导入
        foreach ($list as $key => $value) {
            // 跳过保留key
            if (in_array($key, ['class'])) {
                continue;
            }
            // 属性赋值
            if (is_array($value) && isset($value['class'])) {
                // 获取配置
                $subClass = $value['class'];
                // 实例化
                $subObject = new $subClass();
                // 属性导入
                foreach ($value as $k => $v) {
                    if (in_array($k, ['class'])) {
                        continue;
                    }
                    $subObject->$k = $v;
                }
                $object->$key = $subObject;
            } else {
                $object->$key = $value;
            }
        }
        // 触发初始化事件
        $object->onInitialize();
        $object->setStatus(Component::STATUS_READY);
        // 触发请求开始事件
        $object->onRequestStart();
        $object->setStatus(Component::STATUS_RUNNING);
        // 返回对象
        return $this->$name = $object;
    }

    /**
     * 执行功能并返回
     * @param  string $method
     * @param  string $action
     * @return mixed
     */
    public function runAction($method, $action)
    {
        $action = "{$method} {$action}";
        // 路由匹配
        list($action, $queryParams) = \Mix::app()->route->match($action);
        // 执行功能
        if ($action) {
            // 路由参数导入请求类
            \Mix::app()->request->setRoute($queryParams);
            // index处理
            if (isset($queryParams['controller']) && strpos($action, ':action') !== false) {
                $action = str_replace(':action', 'index', $action);
            }
            // 实例化控制器
            $action    = "{$this->controllerNamespace}\\{$action}";
            $classFull = \mix\base\Route::dirname($action);
            $classPath = \mix\base\Route::dirname($classFull);
            $className = \mix\base\Route::snakeToCamel(\mix\base\Route::basename($classFull), true);
            $method    = \mix\base\Route::snakeToCamel(\mix\base\Route::basename($action), true);
            $class     = "{$classPath}\\{$className}Controller";
            $method    = "action{$method}";
            try {
                $reflect = new \ReflectionClass($class);
            } catch (\ReflectionException $e) {
                throw new \mix\exception\HttpException("Not Found (#404)", 404);
            }
            $controller = $reflect->newInstanceArgs();
            // 判断方法是否存在
            if (method_exists($controller, $method)) {
                // 执行控制器的方法
                return $controller->$method();
            }
        }
        throw new \mix\exception\HttpException("Not Found (#404)", 404);
    }

    /**
     * 获取配置目录路径
     * @return string
     */
    public function getConfigPath()
    {
        return $this->basePath . 'config' . DIRECTORY_SEPARATOR;
    }

    /**
     * 获取运行目录路径
     * @return string
     */
    public function getRuntimePath()
    {
        return $this->basePath . 'runtime' . DIRECTORY_SEPARATOR;
    }

    /**
     * 装载全部组件
     */
    public function loadAllComponent()
    {
        foreach ($this->register as $key => $value) {
            $this->$key;
        }
    }

    /**
     * 清扫组件
     * 只清扫 STATUS_RUNNING 状态的组件
     */
    public function cleanComponent()
    {
        foreach ($this as $name => $attribute) {
            if(is_object($attribute) && $attribute instanceof Component && $attribute->getStatus() == Component::STATUS_RUNNING){
                $attribute->onRequestEnd();
                $attribute->setStatus(Component::STATUS_READY);
            }
        }
    }

}
