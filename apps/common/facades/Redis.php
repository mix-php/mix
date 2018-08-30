<?php

namespace apps\common;

/**
 * Redis 门面类
 * @author 刘健 <coder.liu@qq.com>
 */
class Redis extends \mix\facades\Redis
{

    /**
     * 获取实例集合
     * @return array
     */
    public static function getInstances()
    {
        return [
            'db1' => app('db1')->redis,
        ];
    }

}
