<?php

namespace apps\common\facades;

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
            'default' => app()->redis,
        ];
    }

}
