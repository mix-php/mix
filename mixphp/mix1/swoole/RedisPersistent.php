<?php

namespace mix\swoole;

/**
 * redis长连接组件
 * @author 刘健 <coder.liu@qq.com>
 *
 * @method set($key, $value)
 */
class RedisPersistent extends \mix\nosql\BaseRedisPersistent
{

    // 初始化事件
    public function onInitialize()
    {
        parent::onInitialize();
        // 初始化
        $this->initialize();
    }

}
