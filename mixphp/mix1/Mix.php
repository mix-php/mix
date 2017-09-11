<?php

/**
 * Mix类
 * @author 刘健 <code.liu@qq.com>
 */
class Mix
{

    // App实例
    protected static $app;

    // 主机
    protected static $host;

    // 返回App
    public static function app()
    {
        if (is_object(self::$app)) {
            return self::$app;
        }
        if (is_array(self::$app)) {
            return self::$app[self::$host];
        }
        return null;
    }

    // 设置App
    public static function setApp($app)
    {
        self::$app = $app;
    }

    // 设置Apps
    public static function setApps($apps)
    {
        self::$app = $apps;
    }

    // 设置host
    public static function setHost($host)
    {
        self::$host = null;
        $vHosts = array_keys(self::$app);
        foreach ($vHosts as $vHost) {
            if ($vHost == '*') {
                continue;
            }
            if (preg_match("/{$vHost}/i", $host)) {
                self::$host = $vHost;
                break;
            }
        }
        if (is_null(self::$host)) {
            self::$host = isset(self::$app['*']) ? '*' : array_shift($vHosts);
        }
    }

}
