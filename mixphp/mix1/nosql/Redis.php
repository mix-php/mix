<?php

namespace mix\nosql;

use mix\base\Component;

/**
 * redis组件
 * @author 刘健 <coder.liu@qq.com>
 *
 * @method set($key, $value)
 */
class Redis extends BaseRedis
{

    // 初始化事件
    public function onInitialize()
    {
        parent::onInitialize();
        // 连接
        $this->connect();
    }

}
