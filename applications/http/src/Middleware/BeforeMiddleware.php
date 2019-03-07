<?php

namespace Http\Middleware;

use Mix\Http\Middleware\MiddlewareInterface;

/**
 * Class BeforeMiddleware
 * @package Http\Middleware
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
    public function handle(callable $callback, \Closure $next)
    {
        // 添加中间件执行代码
        list($controller, $action) = $callback;

        // ...

        // 执行下一个中间件
        return $next();
    }

}
