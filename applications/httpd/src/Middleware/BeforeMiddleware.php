<?php

namespace Httpd\Middleware;

use Mix\Http\Middleware\MiddlewareInterface;

/**
 * 前置中间件
 * @author LIUJIAN <coder.keda@gmail.com>
 */
class BeforeMiddleware implements MiddlewareInterface
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
        list($controller, $action) = $callable;
        // ...
        // 执行下一个中间件
        return $next();
    }

}
