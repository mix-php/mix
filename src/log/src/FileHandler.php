<?php

namespace Mix\Log;

use Mix\Bean\BeanInjector;

/**
 * Class FileHandler
 * @package Mix\Log
 * @author liu,jian <coder.keda@gmail.com>
 */
class FileHandler implements LoggerHandlerInterface
{

    /**
     * 轮转规则
     */
    const ROTATE_HOUR = 1;
    const ROTATE_DAY = 2;
    const ROTATE_WEEKLY = 3;

    /**
     * 单文件
     * @var string
     */
    public $single = '';

    /**
     * 日志目录
     * @var string
     */
    public $dir = '';

    /**
     * 日志轮转类型
     * @var int
     */
    public $rotate = self::ROTATE_DAY;

    /**
     * 最大文件尺寸
     * @var int
     */
    public $maxFileSize = 0;

    /**
     * FileHandler constructor.
     * @param array $config
     * @throws \PhpDocReader\AnnotationException
     * @throws \ReflectionException
     */
    public function __construct(array $config = [])
    {
        BeanInjector::inject($this, $config);
    }

    /**
     * 处理日志
     * @param $level
     * @param $message
     */
    public function handle($level, $message)
    {
        $file    = $this->getLogFile($level);
        $message = preg_replace("/\\e\[[0-9]+m/", '', $message); // 过滤颜色
        if (!$file) {
            return;
        }
        error_log($message, 3, $file);
    }

    /**
     * 获取日志文件
     * @param $level
     * @return bool|string
     */
    protected function getLogFile($level)
    {
        // 没有文件信息
        if (!$this->single && !$this->dir) {
            return false;
        }
        // 单文件
        if ($this->single) {
            return $this->single;
        }
        // 生成文件名
        $logDir = $this->dir;
        switch ($this->rotate) {
            case self::ROTATE_HOUR:
                $subDir     = date('Ymd');
                $timeFormat = date('YmdH');
                break;
            case self::ROTATE_DAY:
                $subDir     = date('Ym');
                $timeFormat = date('Ymd');
                break;
            case self::ROTATE_WEEKLY:
                $subDir     = date('Y');
                $timeFormat = date('YW');
                break;
            default:
                $subDir     = '';
                $timeFormat = '';
        }
        if (in_array($level, Constants::LEVELS)) {
            $prefix = '';
        } else {
            $prefix = "{$level}_";
        }
        $filename = $logDir . ($subDir ? DIRECTORY_SEPARATOR . $subDir : '') . DIRECTORY_SEPARATOR . $prefix . ($timeFormat ?: '');
        $file     = "{$filename}.log";
        // 创建目录
        $dir = dirname($file);
        is_dir($dir) or mkdir($dir, 0777, true);
        // 尺寸轮转
        $number = 0;
        while (file_exists($file) && $this->maxFileSize > 0 && filesize($file) >= $this->maxFileSize) {
            $file = "{$filename}_" . ++$number . '.log';
        }
        // 返回
        return $file;
    }

}
