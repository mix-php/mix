<?php

namespace mix\client;

/**
 * PdoPersistent组件
 * @author 刘健 <coder.liu@qq.com>
 */
class PdoPersistent extends BasePdoPersistent
{

    // 初始化事件
    public function onInitialize()
    {
        parent::onInitialize();
        // 初始化
        $this->initialize();
    }

    // 析构事件
    public function onDestruct()
    {
        parent::onDestruct();
        // 关闭连接
        $this->disconnect();
    }

}
