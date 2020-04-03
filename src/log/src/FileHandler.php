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
     * @deprecated 废弃，固定只可使用 ROTATE_DAY
     */
    const ROTATE_HOUR = 1;
    const ROTATE_DAY = 2;
    const ROTATE_WEEKLY = 3;

    /**
     * 单文件
     * @var string
     * @deprecated 废弃，统一使用 filename
     */
    public $single = '';

    /**
     * 日志目录
     * @var string
     * @deprecated 废弃，统一使用 filename
     */
    public $dir = '';

    /**
     * 文件名 (绝对路径)
     * @var string
     */
    public $filename = '';

    /**
     * 是否开启轮转
     * @var bool
     */
    public $rotate = true;

    /**
     * 最大文件尺寸
     * @var int
     */
    public $maxFileSize = 0;

    /**
     * 最大天数
     * @var int
     */
    public $maxDays = 7;

    /**
     * @var string
     */
    protected $today = '';

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
        $file = $this->filename;
        if (!$file) {
            return;
        }

        // 创建目录
        $dir = dirname($file);
        is_dir($dir) or mkdir($dir, 0777, true);

        $this->rotate();

        $message = preg_replace("/\\e\[[0-9]+m/", '', $message); // 过滤颜色
        error_log($message, 3, $file);
    }

    /**
     * 轮转
     */
    protected function rotate()
    {
        if (!$this->rotate) {
            return;
        }
        $file = $this->filename;
        if (!$file || !file_exists($file)) {
            return;
        }

        $today      = date('Ymd');
        $dateString = '';
        if (!$this->today) {
            // 处理进程初次启动，日志文件非当日时间
            $this->today = date('Ymd', filectime($this->filename));
        }
        $info = pathinfo($file);
        $move = 0;
        if ($this->maxFileSize > 0 && filesize($file) >= $this->maxFileSize) {
            $move       = 1;
            $dateString = $today;
        }
        // 再次判断文件时间是为了处理一直在执行的进程，当日期发生变化时，但是文件已经被其他进程轮转过，不可再次轮转
        if ($this->today != $today && date('Ymd', filectime($this->filename)) != $today) {
            $move       = -1;
            $dateString = $this->today;
        }
        $this->today = $today;
        if (!$move) {
            return;
        }

        $lock = sprintf('%s.lock', $this->filename);
        $fp   = fopen($lock, "a+");
        if (!$fp) {
            return;
        }
        if (flock($fp, LOCK_EX)) {
            $number = 0;
            while (file_exists($file)) {
                ++$number;
                $numberString = (string)$number;
                $multiplier   = 3 - strlen($numberString);
                $numberString = str_repeat('0', $multiplier < 0 ? 0 : $multiplier) . $numberString;
                $file         = sprintf(
                    '%s.%s.%s.%s',
                    $info['dirname'] . DIRECTORY_SEPARATOR . $info['filename'],
                    $dateString,
                    $numberString,
                    $info['extension']
                );
            }

            $ok = @rename($this->filename, $file);
            if ($ok and $move == -1) {
                $this->clear();
            }

            flock($fp, LOCK_UN);
            @unlink($lock);
        }
        fclose($fp);
    }

    /**
     * 清理多余的日志文件
     */
    protected function clear()
    {
        $info    = pathinfo($this->filename);
        $prefixs = [];
        for ($i = -$this->maxDays; $i <= -1; $i++) {
            $day       = date('Ymd', strtotime(sprintf('%d day', $i)));
            $prefixs[] = sprintf('%s.%s.', $info['filename'], $day);
        }

        $dir = dirname($this->filename);
        $dh  = @opendir($dir);
        if (!$dh) {
            return;
        }
        while (false !== ($file = readdir($dh))) {
            if (in_array($file, ['.', '..', $info['basename'], sprintf('%s.lock', $info['basename'])])) {
                continue;
            }
            $full = $dir . '/' . $file;
            if (!is_file($full)) {
                continue;
            }
            if (strpos($file, sprintf('%s.', $info['filename'])) !== 0) {
                continue;
            }

            $stet = false;
            foreach ($prefixs as $prefix) {
                if (strpos($file, $prefix) === 0) {
                    $stet = true;
                    break;
                }
            }
            if (!$stet) {
                @unlink($full);
            }
        }
    }

}
