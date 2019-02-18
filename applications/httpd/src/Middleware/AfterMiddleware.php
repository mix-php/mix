<?php

namespace Httpd\Middleware;

use Mix\Http\Middleware\MiddlewareInterface;

/**
 * 后置中间件
 * @author LIUJIAN <coder.keda@gmail.com>
 */
class AfterMiddleware implements MiddlewareInterface
{

    /**
     * 处理
     * @param callable $callback
     * @param \Closure $next
     * @return mixed
     */
    public function handle(callable $callable, \Closure $next)
    {
        // 添加中间件执行代码
        $response = $next();
        list($controller, $action) = $callable;
        // ...
        // 返回响应内容
        return $response;
    }

}
