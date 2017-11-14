<?php

/**
 * App类
 * @author 刘健 <coder.liu@qq.com>
 */

namespace mix\swoole;

class Application extends \mix\web\Application
{

    // 执行功能 (MixHttpd)
    public function run()
    {
        $server  = \Mix::app()->request->server();
        $method  = strtoupper($server['request_method']);
        $action  = empty($server['path_info']) ? '' : substr($server['path_info'], 1);
        $content = $this->runAction($method, $action);
        \Mix::app()->response->setContent($content);
        \Mix::app()->response->send();
        $this->cleanComponents();
    }

}
