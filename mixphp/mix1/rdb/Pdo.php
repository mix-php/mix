<?php

namespace mix\rdb;

/**
 * Pdo组件
 * @author 刘健 <coder.liu@qq.com>
 */
class Pdo extends BasePdo
{

    // 请求结束事件
    public function onRequestEnd()
    {
        parent::onRequestEnd();
        // 关闭连接
        $this->close();
    }

    // 析构事件
    public function onDestruct()
    {
        parent::onDestruct();
        // 关闭连接
        $this->close();
    }

}
