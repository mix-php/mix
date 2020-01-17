<?php

namespace Mix\Helper;

/**
 * PhpHelper类
 * @author liu,jian <coder.keda@gmail.com>
 */
class PhpHelper
{

    /**
     * 是否为 CLI 模式
     * @return bool
     */
    public static function isCli()
    {
        return PHP_SAPI === 'cli';
    }

    /**
     * 是否为 Win 系统
     * @return bool
     */
    public static function isWin()
    {
        if (static::isMac()) {
            return false;
        }
        return stripos(PHP_OS, 'WIN') !== false;
    }

    /**
     * 是否为 Mac 系统
     * @return bool
     */
    public static function isMac()
    {
        return stripos(PHP_OS, 'Darwin') !== false;
    }

}
