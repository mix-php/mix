<?php

/**
 * Log类
 * @author 刘健 <code.liu@qq.com>
 */

namespace mix\base;

class Log extends Object
{

    // 轮转规则
    const ROTATE_HOUR = 0;
    const ROTATE_DAY = 1;
    const ROTATE_WEEKLY = 2;

    // 日志目录
    public $logDir = 'logs';

    // 日志记录级别
    public $level = ['error', 'info', 'debug'];

    // 日志轮转类型
    public $logRotate = self::ROTATE_DAY;

    // 最大文件尺寸
    public $maxFileSize = 2048 * 1024;

    // 调试日志
    public function debug($message)
    {
        in_array('debug', $this->level) and $this->write('debug', $message);
    }

    // 信息日志
    public function info($message)
    {
        in_array('info', $this->level) and $this->write('info', $message);
    }

    // 错误日志
    public function error($message)
    {
        in_array('error', $this->level) and $this->write('error', $message);
    }

    // 写入信息
    public function write($level, $message)
    {
        switch ($this->logRotate) {
            case self::ROTATE_HOUR:
                $timeFormat = date('YmdH');
                break;
            case self::ROTATE_DAY:
                $timeFormat = date('Ymd');
                break;
            case self::ROTATE_WEEKLY:
                $timeFormat = date('YW');
                break;
            default :
                $timeFormat = date('Ymd');
                break;
        }
        $filename = "{$level}_{$timeFormat}";
        $dir = \Mix::$app->getRuntimePath() . $this->logDir;
        is_dir($dir) or mkdir($dir);
        $file = $dir . DS . $filename . '.log';
        $number = 0;
        while (file_exists($file) && filesize($file) >= $this->maxFileSize) {
            $file = $dir . DS . $filename . '_' . ++$number . '.log';
        }
        file_put_contents($file, $message, FILE_APPEND);
    }

}
