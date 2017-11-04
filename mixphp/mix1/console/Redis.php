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
        $this->connect();
    }

}
