<?php

/**
 * Cookie类
 * @author 刘健 <code.liu@qq.com>
 */

namespace sys\web;

class Cookie
{

    // 是否初始化完成
    private static $initComplete;

    // 配置
    private static $conf;

    // 初始化
    public static function init($conf = null)
    {
        if (!isset(self::$initComplete)) {
            self::$conf = Config::get('main.cookie');
            self::$initComplete = true;
        }
        is_null($conf) or self::$conf = array_merge(self::$conf, $conf);
    }

    // 取值
    public static function get($name = null)
    {
        self::init();
        if (is_null($name)) {
            $tmp = [];
            foreach ($_COOKIE as $name => $value) {
                $tmp[] = self::getValue($name, $_COOKIE[$name], self::$conf['signature_key']);
            }
            return $tmp;
        }
        if (isset($_COOKIE[$name])) {
            return self::getValue($name, $_COOKIE[$name], self::$conf['signature_key']);
        }
        return null;
    }

    // 赋值
    public static function set($name, $value, $uExpire = null)
    {
        self::init();
        // 定义配置变量
        extract(self::$conf);
        // 赋值
        $value = self::signature($name, $value, $signature_key);
        $expire = is_null($uExpire) ? $expire : $uExpire;
        setcookie($name, $value, time() + $expire, $domain, $secure, $httponly);
    }

    // 判断是否存在
    public static function has($name)
    {
        self::init();
        return isset($_COOKIE[$name]);
    }

    // 删除
    public static function delete($name)
    {
        self::set($name, null);
    }

    // 清空当前域所有cookie
    public static function clear()
    {
        foreach ($_COOKIE as $name => $value) {
            self::set($name, null);
        }
    }

    // 签名
    private static function signature($name, $value, $signatureKey)
    {
        if ($signatureKey == '' || is_null($value) || $value == '') {
            return $value;
        }
        $signature = substr(md5($name . $value . $signatureKey), 8, 16);
        return $value . '|' . $signature;
    }

    // 获取签名效验后的值
    private static function getValue($name, $value, $signatureKey)
    {
        if ($signatureKey == '') {
            return $value;
        }
        $tmp = explode('|', $value);
        if (count($tmp) == 2) {
            if (self::signature($name, $unValue = array_shift($tmp), $signatureKey) == $value) {
                return $unValue;
            }
        }
        return null;
    }

}
