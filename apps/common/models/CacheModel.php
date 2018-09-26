<?php

namespace apps\common\models;

use mix\facades\Redis;

/**
 * 缓存模型类
 * 这是一个是关于缓存操作的数据模型范例 (nosql数据库)
 * 使用缓存来处理高并发，是流行的做法，使用一个模型来统一调用是更好的方式
 * 数据模型是使用组件操作数据库，所以不需要继承任何基类
 * @author 刘健 <coder.liu@qq.com>
 */
class CacheModel
{

    // 设置缓存
    public function setUserinfo($value)
    {
        $key     = 'KEY';
        $success = Redis::setex($key, 7200, $value);
        return $success;
    }

    // 获取缓存
    public function getUserinfo()
    {
        $key   = 'KEY';
        $value = Redis::get($key);
        return empty($value) ? null : $value;
    }

}