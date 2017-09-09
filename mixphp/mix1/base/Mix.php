<?php

/**
 * Mix类
 * @author 刘健 <code.liu@qq.com>
 */
class Mix
{

    // App实例
    public static $app;

    // 主机
    public static $host;

    // 返回App
    public static function app()
    {
        if (is_object(self::$app)) {
            return self::$app;
        }
        if (is_array(self::$app)) {
            if (isset(self::$app[self::$host])) {
                return self::$app[self::$host];
            }
            if (isset(self::$app['*'])) {
                return self::$app['*'];
            }
            return array_shift(self::$app);
        }
        return null;
    }

}
