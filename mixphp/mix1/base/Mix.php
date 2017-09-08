<?php

/**
 * Mix类
 * @author 刘健 <code.liu@qq.com>
 */

class Mix
{

    // App实例
    public static $_app;

    // 返回App
    public static function app()
    {
        return self::$_app;
    }

}
