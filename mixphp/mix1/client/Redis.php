<?php

namespace mix\client;

/**
 * redis组件
 * @author 刘健 <coder.liu@qq.com>
 *
 * @method set($key, $value)
 */
class Redis extends BaseRedis
{

    // 请求结束事件
    public function onRequestEnd()
    {
        parent::onRequestEnd();
        // 关闭连接
        $this->disconnect();
    }

    // 析构事件
    public function onDestruct()
    {
        parent::onDestruct();
        // 关闭连接
        $this->disconnect();
    }

}
