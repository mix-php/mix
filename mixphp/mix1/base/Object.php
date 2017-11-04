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
        $this->onConstruct();
        if (!empty($config)) {
            foreach ($config as $key => $value) {
                $this->$key = $value;
            }
        }
        $this->onInitialize();
    }

    // 构造事件
    public function onConstruct()
    {
    }

    // 初始化事件
    public function onInitialize()
    {
    }

    // 析构事件
    public function onDestruct()
    {
    }

    // 析构
    public function __destruct()
    {
        $this->onDestruct();
    }

}
