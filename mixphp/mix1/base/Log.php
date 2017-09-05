<?php

/**
 * Log类
 * @author 刘健 <code.liu@qq.com>
 */

namespace mix\base;

class Log extends Object
{

    // 写入错误信息
    public function error($message)
    {
        $this->write('error', $message);
    }

    // 写入
    public function write($type, $message)
    {
        $timeFormat = date('Ymd');
        $filename = "{$type}_{$timeFormat}";
        $dir = \Mix::$app->getRuntimePath() . 'logs';
        $path = $dir . DS . $filename . '.log';
        if (!is_dir($dir)) {
            mkdir($dir);
        }
        file_put_contents($path, $message, FILE_APPEND);
    }

}
