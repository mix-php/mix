<?php

namespace mix\base;

/**
 * 对象基类
 * @author 刘健 <coder.liu@qq.com>
 */
class Object
{

    // 构造
    public function __construct($config = [])
    {
        method_exists($this, 'onConstruct') and $this->onConstruct();
    }

    // 析构
    public function __destruct()
    {
        method_exists($this, 'onDestruct') and $this->onDestruct();
    }

}
