<?php

namespace mix\swoole;

/**
 * Pdo长连接组件
 * @author 刘健 <coder.liu@qq.com>
 */
class PdoPersistent extends \mix\rdb\BasePdoPersistent
{

    // 初始化事件
    public function onInitialize()
    {
        parent::onInitialize();
        // 初始化
        $this->initialize();
    }

}
