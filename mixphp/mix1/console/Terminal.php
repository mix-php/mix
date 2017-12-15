<?php

namespace mix\console;

use mix\base\BaseObject;

/**
 * 终端类
 * @author 刘健 <coder.liu@qq.com>
 */
class Terminal extends BaseObject
{

    const COLOR_DEFAULT = "\033[0m";
    const COLOR_RED = "\033[31m";
    const COLOR_GREEN = "\033[32m";
    const COLOR_YELLOW = "\033[33m";
    const COLOR_BLUE = "\033[34m";
    const STYLE_BOLD = "\033[1m";

    protected $isWin;

    public function onConstruct()
    {
        $this->isWin = $this->isWin();
    }

    // 是否为win操作系统
    public function isWin()
    {
        return substr(PHP_OS, 0, 3) === 'WIN' ? true : false;
    }

    // 输出
    public function output($message, $color = self::COLOR_DEFAULT)
    {
        if ($this->isWin) {
            echo $message;
            return;
        }
        echo $color . $message . self::COLOR_DEFAULT;
    }

}