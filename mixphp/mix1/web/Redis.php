<?php

namespace mix\web;

/**
 * redis组件
 * @author 刘健 <coder.liu@qq.com>
 *
 * @method set($key, $value)
 */
class Redis extends \mix\nosql\Redis
{

    // 请求开始事件
    public function onRequestStart()
    {
        $this->connect();
    }

    // 请求结束事件
    public function onRequestEnd()
    {
        $this->_redis = null;
    }

}
