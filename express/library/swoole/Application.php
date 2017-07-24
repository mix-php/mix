<?php

/**
 * App类
 * @author 刘健 <code.liu@qq.com>
 */

namespace express\swoole;

class Application extends \express\base\Application
{

    /**
     * 执行功能 (LNSMP架构)
     */
    public function run($requester, $responder)
    {
        $request = \Express::$app->request->setRequester($requester);
        $response = \Express::$app->response->setResponder($responder);
        $method = strtoupper($requester->header['request_method']);
        $action = empty($requester->header['pathinfo']) ? '' : substr($requester->header['pathinfo'], 1);
        $content = $this->runAction($method, $action, $request, $response);
        $response->setContent($content)->send();
    }

    /**
     * 执行功能并返回
     * @param  string $action
     * @param  array $inout
     * @return mixed
     */
    public function runAction($method, $action, $request, $response)
    {
        $action = "{$method} {$action}";
        // 路由匹配
        list($action, $urlParams) = \Express::$app->route->match($action);
        // 执行功能
        if ($action) {
            // 路由参数导入请求类
            $request->setRoute($urlParams);
            // index处理
            if (isset($urlParams['controller']) && strpos($action, ':action') !== false) {
                $action = str_replace(':action', 'index', $action);
            }
            // 实例化控制器
            $action = "{$this->controllerNamespace}\\{$action}";
            $classFull = dirname($action);
            $classPath = dirname($classFull);
            $className = \Express::$app->route->snakeToCamel(basename($classFull));
            $method = \Express::$app->route->snakeToCamel(basename($action), true);
            $class = "{$classPath}\\{$className}Controller";
            $method = "action{$method}";
            try {
                $reflect = new \ReflectionClass($class);
            } catch (\ReflectionException $e) {
                throw new \express\exception\HttpException("URL不存在", 404);
            }
            $controller = $reflect->newInstanceArgs();
            // 判断方法是否存在
            if (method_exists($controller, $method)) {
                // 执行控制器的方法
                return $controller->$method($request, $response);
            }
        }
        throw new \express\exception\HttpException("URL不存在", 404);
    }

}
