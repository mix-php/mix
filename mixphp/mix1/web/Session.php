<?php

/**
 * Session类
 * @author 刘健 <code.liu@qq.com>
 */

namespace mix\web;

use mix\base\Object;

class Session extends Object
{

    // 处理者值
    const HANDLER_FILES = 'files';
    const HANDLER_MEMCACHE = 'memcache';
    const HANDLER_REDIS = 'redis';
    // 处理者
    public $saveHandler = self::HANDLER_FILES;
    // 保存路径
    public $savePath = '';
    // 生存时间
    public $gcMaxLifetime = 7200;
    // session名
    public $name = 'MIX_SSID';

    // 初始化
    public function init()
    {
        if (session_status() != PHP_SESSION_ACTIVE) {
            ini_set('session.save_handler', $this->saveHandler);
            ini_set('session.save_path', $this->savePath);
            ini_set('session.gc_maxlifetime', $this->gcMaxLifetime);
            ini_set('session.name', $this->name);
            session_start();
        }
    }

    // 取值
    public function get($name = null)
    {
        if (is_null($name)) {
            return $_SESSION;
        }
        return isset($_SESSION[$name]) ? $_SESSION[$name] : null;
    }

    // 赋值
    public function set($name, $value)
    {
        $_SESSION[$name] = $value;
    }

    // 判断是否存在
    public function has($name)
    {
        return isset($_SESSION[$name]);
    }

    // 删除
    public function delete($name)
    {
        unset($_SESSION[$name]);
    }

    // 清除session
    public function clear()
    {
        session_unset();
        session_destroy();
    }

}
