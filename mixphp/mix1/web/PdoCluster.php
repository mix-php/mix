<?php

namespace mix\web;

use mix\base\Component;

/**
 * PdoCluster组件
 * @author 刘健 <coder.liu@qq.com>
 */
class PdoCluster extends \mix\rdb\PdoCluster
{

    // 请求开始事件
    public function onRequestStart()
    {
        parent::onRequestStart();
    }

    // 请求结束事件
    public function onRequestEnd()
    {
        parent::onRequestEnd();
        // 关闭连接
        $this->close();
    }

}
