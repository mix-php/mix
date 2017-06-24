<?php

/**
 * Session类
 * @author 刘健 <code.liu@qq.com>
 */

namespace sys\web;

class Session
{

    // 是否初始化完成
    private static $initComplete;

    // 初始化
    private static function init()
    {
        if (!isset(self::$initComplete)) {
            $config = Config::get('main.session');
            ini_set('session.save_handler', $config['save_handler']);
            ini_set('session.gc_maxlifetime', $config['gc_maxlifetime']);            
            ini_set('session.name', $config['name']);
            switch ($config['save_handler']) {
                case 'files':
                    ini_set('session.save_path', $config['files_save_path']);
                    break;
                case 'redis':
                    ini_set('session.save_path', 'tcp://' . Config::get('redis.hostname') . ':' . Config::get('redis.hostport') . '?auth=' . Config::get('redis.password'));
                    break;
                case 'memcache':
                    ini_set('session.save_path', 'tcp://' . Config::get('memcache.hostname') . ':' . Config::get('memcache.hostport') . '?auth=' . Config::get('memcache.password'));
                    break;
            }
            session_start();
            self::$initComplete = true;
        }
    }

    // 取值
    public static function get($name = null)
    {
        self::init();
        if (is_null($name)) {
            return $_SESSION;
        }
        return isset($_SESSION[$name]) ? $_SESSION[$name] : null;
    }

    // 赋值
    public static function set($name, $value)
    {
        self::init();
        $_SESSION[$name] = $value;
    }

    // 判断是否存在
    public static function has($name)
    {
        self::init();
        return isset($_SESSION[$name]);
    }

    // 删除
    public static function delete($name)
    {
        self::init();
        unset($_SESSION[$name]);
    }

    // 清除session
    public static function clear()
    {
        self::init();
        session_unset();
        session_destroy();
    }

}
