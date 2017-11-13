<?php

namespace mix\console;

/**
 * Pdo组件
 * @author 刘健 <coder.liu@qq.com>
 */
class Pdo extends \mix\rdb\Pdo
{

    // 初始化事件
    public function onInitialize()
    {
        parent::onInitialize();
        $this->connect();
    }

}
