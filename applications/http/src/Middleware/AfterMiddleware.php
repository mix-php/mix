<?php

namespace Http\Middleware;

use Mix\Core\Middleware\MiddlewareInterface;

/**
 * Class AfterMiddleware
 * @package Http\Middleware
 * @author liu,jian <coder.keda@gmail.com>
 */
class AfterMiddleware implements MiddlewareInterface
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
        $response = $next();
        list($controller, $action) = $callback;

        // ...

        // 返回响应内容
        return $response;
    }

}
