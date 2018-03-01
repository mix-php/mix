<?php

/**
 * Mix类
 * @author 刘健 <coder.liu@qq.com>
 */
class Mix
{

    // App实例
    protected static $_app;

    // 主机
    protected static $_host;

    // 公共容器
    public static $container;

    /**
     * 返回App，并设置组件命名空间
     *
     * @return \mix\swoole\Application|\mix\web\Application|\mix\console\Application
     */
    public static function app($namespace = null)
    {
        // 获取App
        $app = self::getApp();
        if (is_null($app)) {
            return $app;
        }
        // 设置组件命名空间
        $app->setComponentNamespace($namespace);
        // 返回App
        return $app;
    }

    /**
     * 获取App
     *
     * @return \mix\swoole\Application|\mix\web\Application|\mix\console\Application
     */
    protected static function getApp()
    {
        if (is_object(self::$_app)) {
            return self::$_app;
        }
        if (is_array(self::$_app)) {
            return self::$_app[self::$_host];
        }
        return null;
    }

    // 设置App
    public static function setApp($app)
    {
        self::$_app = $app;
    }

    // 设置Apps
    public static function setApps($apps)
    {
        self::$_app = $apps;
    }

    // 设置host
    public static function setHost($host)
    {
        self::$_host = null;
        $vHosts      = array_keys(self::$_app);
        foreach ($vHosts as $vHost) {
            if ($vHost == '*') {
                continue;
            }
            if (preg_match("/{$vHost}/i", $host)) {
                self::$_host = $vHost;
                break;
            }
        }
        if (is_null(self::$_host)) {
            self::$_host = isset(self::$_app['*']) ? '*' : array_shift($vHosts);
        }
    }

    // 使用配置创建新对象
    public static function createObject($config)
    {
        // 构建属性数组
        foreach ($config as $key => $value) {
            // 子类实例化
            if (is_array($value) && isset($value['class'])) {
                $subClass = $value['class'];
                unset($value['class']);
                $config[$key] = new $subClass($value);
            }
        }
        // 实例化
        $class = $config['class'];
        unset($config['class']);
        return new $class($config);
    }

}
