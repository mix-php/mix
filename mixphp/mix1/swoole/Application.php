<?php

namespace mix\swoole;

/**
 * App类
 * @author 刘健 <coder.liu@qq.com>
 */
class Application extends \mix\web\Application
{

    // 执行功能 (mix-httpd)
    public function run()
    {
        \mix\web\Error::register();
        $server = \Mix::app()->request->server();
        $method = strtoupper($server['request_method']);
        $action = empty($server['path_info']) ? '' : substr($server['path_info'], 1);
        \Mix::app()->response->content = $this->runAction($method, $action);
        \Mix::app()->response->send();
        $this->cleanComponents();
    }

}
