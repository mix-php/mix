<?php

namespace apps\httpd\middleware;

/**
 * 后置中间件
 * @author 刘健 <coder.liu@qq.com>
 */
class AfterMiddleware
{

    public function handle($callable, \Closure $next)
    {
        // 添加中间件执行代码
        $response = $next();
        list($controller, $action) = $callable;
        // ...
        // 返回响应内容
        return $response;
    }

}
