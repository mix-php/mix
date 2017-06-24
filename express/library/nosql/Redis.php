<?php

/**
 * redis 类
 * @author 刘健 <code.liu@qq.com>
 */

namespace sys\nosql;

use sys\Config;

class Redis
{

    // redis对象
    private static $redis;

    // 配置信息
    private static $config;

    // 配置
    public static function config()
    {
        self::$config = Config::get('redis');
    }

    // 连接redis服务器
    public static function connect()
    {
        if (!isset(self::$redis)) {
            self::config();
            $redis = new \Redis();
            // connect 这里如果设置timeout，是全局有效的，执行brPop时会受影响
            if (!$redis->connect(self::$config['host'], self::$config['port'])) {
                throw new \Exception('Redis Connect Failure', 1);
            }
            $redis->auth(self::$config['password']);
            $redis->select(self::$config['database']);
            self::$redis = $redis;
        }
        return self::$redis;
    }

    public static function __callStatic($name, $arguments)
    {
        $redis = self::connect();
        return call_user_func_array([$redis, $name], $arguments);
    }

}
