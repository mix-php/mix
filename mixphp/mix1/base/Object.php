<?php

/**
 * 对象基类
 * @author 刘健 <code.liu@qq.com>
 */

namespace mix\base;

class Object
{

    public function __construct($config = [])
    {
        // 执行初始化方法
        if (empty($config['disableInit'])) {
            method_exists($this, 'init') and $this->init();
        }
    }

}
