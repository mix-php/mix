<?php

namespace apps\common\models;

/**
 * Index 表单模型类
 * 这是一个是关于队列操作的数据模型范例 (nosql数据库)
 * 使用队列来处理高并发，是流行的做法，使用一个模型来统一调用是更好的方式
 * 数据模型是使用组件操作数据库，所以不需要继承任何基类
 * @author 刘健 <coder.liu@qq.com>
 */
class QueueModel
{

    // 推送数据至队列
    public function push($data)
    {
        $success = \Mix::app()->redis->lpush('KEY', serialize($data));
        return $success;
    }

    // 从队列中取出一条数据，堵塞模式
    public function pop()
    {
        $value = \Mix::app()->redis->brpop('KEY', 30);
        if (empty($value)) {
            return null;
        }
        return unserialize(array_pop($value));
    }

}