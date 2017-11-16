<?php

namespace mix\web;

/**
 * redis组件
 * @author 刘健 <coder.liu@qq.com>
 *
 * @method set($key, $value)
 */
class Redis extends \mix\nosql\BaseRedis
{

    // 初始化事件
    public function onInitialize()
    {
        parent::onInitialize();
    }

    // 请求开始事件
    public function onRequestStart()
    {
        parent::onRequestStart();
        // 连接
        $this->connect();
    }

    // 请求结束事件
    public function onRequestEnd()
    {
        parent::onRequestEnd();
        // 关闭连接
        $this->close();
    }

}
