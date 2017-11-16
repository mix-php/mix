<?php

namespace mix\rdb;

use mix\base\Component;

/**
 * Pdo组件
 * @author 刘健 <coder.liu@qq.com>
 */
class Pdo extends BasePdo
{

    // 初始化事件
    public function onInitialize()
    {
        parent::onInitialize();
        // 连接
        $this->connect();
    }

}
