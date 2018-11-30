<?php

namespace Apps\Common\Facades;

/**
 * RDB 门面类
 * @author 刘健 <coder.liu@qq.com>
 */
class PDO extends \Mix\Facades\PDO
{

    /**
     * 获取实例集合
     * @return array
     */
    public static function getInstances()
    {
        return [
            'default' => app()->pdo,
        ];
    }

}
