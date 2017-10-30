<?php

namespace mix\web;

/**
 * Mysql组件
 * @author 刘健 <coder.liu@qq.com>
 */
class Pdo extends \mix\rdb\Pdo
{

    // 请求开始事件
    public function onRequestStart()
    {
        $this->connect();
    }

    // 请求结束事件
    public function onRequestEnd()
    {
        $this->_pdoStatement = null;
        $this->_pdo          = null;
    }

}
