<?php

namespace mix\console;

/**
 * redis组件
 * @author 刘健 <coder.liu@qq.com>
 *
 * @method set($key, $value)
 */
class Redis extends \mix\nosql\Redis
{

    // 初始化事件
    public function onInitialize()
    {
        parent::onInitialize();
        // 连接
        $this->connect();
    }

    // 析构事件
    public function onDestruct()
    {
        parent::onDestruct();
        // 关闭连接
        $this->close();
    }

}
