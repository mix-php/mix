<?php

namespace Monolog\Handler;

/**
 * 重写写系统方法，使其失效
 */
if (!function_exists('Monolog\Handler\set_error_handler')) {
    function set_error_handler($call)
    {
    }
}

/**
 * 重写系统方法，使其失效
 */
if (!function_exists('Monolog\Handler\restore_error_handler')) {
    function restore_error_handler()
    {
    }
}

namespace Mix\Monolog\Handler;

use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Logger;
use Monolog\Utils;

/**
 * Class RotatingFileHandler
 * @package Mix\Log\Handler
 */
class RotatingFileHandler extends \Monolog\Handler\RotatingFileHandler
{

    /**
     * @param string $filename
     * @param int $maxFiles The maximal amount of files to keep (0 means unlimited)
     * @param string|int $level The minimum logging level at which this handler will be triggered
     * @param bool $bubble Whether the messages that are handled can bubble up the stack or not
     * @param int|null $filePermission Optional file permissions (default (0644) are only for owner read/write)
     * @param bool $useLocking Try to lock log file before doing any writes
     */
    public function __construct(string $filename, int $maxFiles = 0, $level = Logger::DEBUG, bool $bubble = true, ?int $filePermission = null, bool $useLocking = false)
    {
        parent::__construct($filename, $maxFiles, $level, $bubble, $filePermission, $useLocking);

        if ($mtime = @filemtime($this->filename)) {
            $this->nextRotation = date_create_immutable(date('Y-m-d H:i:s', $mtime));
            $this->rotate();
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function getDefaultFormatter(): FormatterInterface
    {
        return new LineFormatter("[%datetime%] %channel%.%level_name%: [%file_line%] %message%\n", 'Y-m-d H:i:s', true);
    }

    /**
     * @return string
     */
    protected function getTimedFilename(): string
    {
        return $this->filename;
    }

    /**
     * @return string
     */
    protected function getTimedFilenameBy(\DateTimeImmutable $dateTime): string
    {
        $fileInfo      = pathinfo($this->filename);
        $timedFilename = str_replace(
            ['{filename}', '{date}'],
            [$fileInfo['filename'], date($this->dateFormat, $dateTime->getTimestamp())],
            $fileInfo['dirname'] . '/' . $this->filenameFormat
        );

        if (!empty($fileInfo['extension'])) {
            $timedFilename .= '.' . $fileInfo['extension'];
        }

        return $timedFilename;
    }

    /**
     * Rotates the files.
     */
    protected function rotate(): void
    {
        // rotate
        $today    = new \DateTimeImmutable('today');
        $tomorrow = new \DateTimeImmutable('tomorrow');
        if ($this->nextRotation < $today || $this->nextRotation < $tomorrow) {
            if ($file = @fopen($this->filename, 'r+')) {
                // 条件2：处理其他进程已经轮转过的情况
                if (
                    flock($file, LOCK_EX) &&
                    @filemtime($this->filename) < $today->getTimestamp()
                ) {
                    $filename = $this->getTimedFilenameBy($this->nextRotation);
                    if (!file_exists($filename)) {
                        @copy($this->filename, $filename);
                        ftruncate($file, 0);
                    }
                }
                flock($file, LOCK_UN);
                fclose($file);
            }
            $this->nextRotation = new \DateTimeImmutable('tomorrow');
        }

        // skip GC of old logs if files are unlimited
        if (0 === $this->maxFiles) {
            return;
        }

        $logFiles = glob($this->getGlobPattern());
        if ($this->maxFiles >= count($logFiles)) {
            // no files to remove
            return;
        }

        // Sorting the files by name to remove the older ones
        usort($logFiles, function ($a, $b) {
            return strcmp($b, $a);
        });

        foreach (array_slice($logFiles, $this->maxFiles) as $file) {
            if (is_writable($file)) {
                // suppress errors here as unlink() might fail if two processes
                // are cleaning up/rotating at the same time
                @unlink($file);
            }
        }

        $this->mustRotate = false;
    }

}
