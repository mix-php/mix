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
        $list  = $this->register[$name];
        $class = $list['class'];
        // 返回新对象
        if (isset($list['singleton']) && $list['singleton'] == false) {
            // 实例化
            $object = new $class();
            // 属性导入
            foreach ($list as $key => $value) {
                if (in_array($key, ['class', 'singleton'])) {
                    continue;
                }
                $object->$key = $value;
            }
            return $object;
        }
        // 返回单例
        if (!isset($this->$name)) {
            // 实例化
            $object = new $class();
            // 属性导入
            foreach ($list as $key => $value) {
                if (in_array($key, ['class', 'singleton'])) {
                    continue;
                }
                $object->$key = $value;
            }
            $this->$name = $object;
        }
        return $this->$name;
    }

    /**
     * 执行功能 (LAMP架构)
     */
    public function run()
    {
        $action  = empty($_SERVER['PATH_INFO']) ? '' : substr($_SERVER['PATH_INFO'], 1);
        $content = $this->runAction($action);
        \Express::$app->response->setContent($content)->send();
    }

    public function runSwooleAction($requester, $responder)
    {
        $request  = \Express::$app->swooleRequest->setRequester($requester);
        $response = \Express::$app->swooleResponse->setResponder($responder);
        $action   = empty($requester->header['pathinfo']) ? '' : substr($requester->header['pathinfo'], 1);
        $content  = $this->runAction($action, ['request' => $request, 'response' => $response]);
        $response->setContent($content)->send();
    }

    /**
     * 执行功能并返回
     * @param  string $action
     * @param  array  $inout
     * @return mixed
     */
    public function runAction($action, $inout = [])
    {
        $method = empty($_SERVER['REQUEST_METHOD']) ? (PHP_SAPI == 'cli' ? 'CLI' : '') : $_SERVER['REQUEST_METHOD'];
        $action = "{$method} {$action}";
        // 路由匹配
        list($action, $urlParams) = \Express::$app->route->match($action);
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
        // 执行
        if ($action) {
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
                throw new \express\exception\RouteException('控制器未找到', $class);
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
        throw new \express\exception\HttpException(404, 'URL不存在');
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
