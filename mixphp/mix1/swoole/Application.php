<?php

/**
 * App类
 * @author 刘健 <coder.liu@qq.com>
 */

namespace mix\swoole;

class Application extends \mix\web\Application
{

    /**
     * 执行功能 (Swoole架构)
     */
    public function run()
    {
        $this->triggerRequestStart();
        $server  = \Mix::app()->request->server();
        $method  = strtoupper($server['request_method']);
        $action  = empty($server['path_info']) ? '' : substr($server['path_info'], 1);
        $content = $this->runAction($method, $action);
        \Mix::app()->response->setContent($content)->send();
        $this->triggerRequestEnd();
    }

    /**
     * 触发开始事件
     */
    public function triggerRequestStart()
    {
        foreach ($this as $key => $value) {
            if (is_object($value) && $value instanceof \mix\base\Component && method_exists($value, 'onRequestStart')) {
                $value->onRequestStart();
            }
        }
    }

    /**
     * 触发结束事件
     */
    public function triggerRequestEnd()
    {
        foreach ($this as $key => $value) {
            if (is_object($value) && $value instanceof \mix\base\Component && method_exists($value, 'onRequestEnd')) {
                $value->onRequestEnd();
            }
        }
    }

}
