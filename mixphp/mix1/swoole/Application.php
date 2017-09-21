<?php

/**
 * App类
 * @author 刘健 <code.liu@qq.com>
 */

namespace mix\swoole;

class Application extends \mix\base\Application
{

    /**
     * 执行功能 (Swoole架构)
     */
    public function run($requester)
    {
        $method = strtoupper($requester->server['request_method']);
        $action = empty($requester->server['path_info']) ? '' : substr($requester->server['path_info'], 1);
        $content = $this->runAction($method, $action);
        \Mix::app()->response->setContent($content)->send();
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
