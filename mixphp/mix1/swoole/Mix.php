<?php

/**
 * Mix类
 * @author 刘健 <code.liu@qq.com>
 */

class Mix
{

    // App实例池
    public static $apps = [];

    // 主机名称
    public static $host;

    // 返回App
    public static function app()
    {
        if (isset(self::$apps[self::$host])) {
            return self::$apps[self::$host];
        }
        if (isset(self::$apps['*'])) {
            return self::$apps['*'];
        }
        return array_shift(self::$apps);
    }

}
