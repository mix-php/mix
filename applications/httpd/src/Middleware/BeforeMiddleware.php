<?php

namespace Httpd\Middleware;

/**
 * 前置中间件
 * @author LIUJIAN <coder.keda@gmail.com>
 */
class BeforeMiddleware
{

    public function handle($callable, \Closure $next)
    {
        // 添加中间件执行代码
        list($controller, $action) = $callable;
        // ...
        // 执行下一个中间件
        return $next();
    }

}
