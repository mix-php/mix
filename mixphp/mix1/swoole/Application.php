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
        $server = \Mix::app()->request->server();
        $method = strtoupper($server['request_method']);
        $action = empty($server['path_info']) ? '' : substr($server['path_info'], 1);
        $this->startEvent();
        $content = $this->runAction($method, $action);
        $this->endEvent();
        \Mix::app()->response->setContent($content)->send();
    }

    /**
     * 触发开始事件
     */
    public function startEvent()
    {
        foreach ($this as $key => $value) {
            if (is_object($value) && $value instanceof \mix\base\Component && method_exists($value, 'onStart')) {
                $value->onStart();
            }
        }
    }

    /**
     * 触发结束事件
     */
    public function endEvent()
    {
        foreach ($this as $key => $value) {
            if (is_object($value) && $value instanceof \mix\base\Component && method_exists($value, 'onEnd')) {
                $value->onEnd();
            }
        }
    }

}
