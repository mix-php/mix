<?php

namespace Apps\Common\Facades;

/**
 * Redis 门面类
 * @author 刘健 <coder.liu@qq.com>
 */
class Redis extends \Mix\Facades\Redis
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
