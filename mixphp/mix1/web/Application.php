<?php

namespace mix\web;

/**
 * App类
 * @author 刘健 <coder.liu@qq.com>
 */
class Application extends \mix\base\Application
{

    /**
     * 执行功能 (LAMP|LNMP|WAMP架构)
     */
    public function run()
    {
        \Mix::app()->error->register();
        $server  = \Mix::app()->request->server();
        $method  = strtoupper($server['request_method']);
        $action  = empty($server['path_info']) ? '' : substr($server['path_info'], 1);
        $content = $this->runAction($method, $action);
        \Mix::app()->response->setContent($content)->send();
        $this->cleanComponents();
    }

    /**
     * 获取公开目录路径
     * @return string
     */
    public function getPublicPath()
    {
        return $this->basePath . 'public' . DIRECTORY_SEPARATOR;
    }

    /**
     * 获取视图目录路径
     * @return string
     */
    public function getViewPath()
    {
        return $this->basePath . 'view' . DIRECTORY_SEPARATOR;
    }

}
