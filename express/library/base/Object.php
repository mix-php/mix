<?php

/**
 * 对象基类
 * @author 刘健 <code.liu@qq.com>
 */

namespace express\base;

class Object
{

    public function __construct()
    {
        // 执行初始化
        method_exists($this, 'init') and $this->init();
    }

}
