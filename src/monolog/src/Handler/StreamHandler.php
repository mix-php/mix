<?php

namespace Mix\Monolog\Handler;

use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\LineFormatter;

/**
 * 重写写系统方法，使其失效
 * @param callable $call
 */
function set_error_handler(callable $call)
{
}

/**
 * 重写系统方法，使其失效
 */
function restore_error_handler()
{
}

/**
 * 重写系统方法，屏蔽异常
 */
function fopen($filename, $mode, $use_include_path = null, $context = null)
{
    return @\fopen($filename, $mode, $use_include_path, $context);
}

/**
 * 重写系统方法，屏蔽异常
 */
function mkdir($pathname, $mode = 0777, $recursive = false, $context = null)
{
    return @\mkdir($pathname, $mode, $recursive, $context);
}

/**
 * Class StreamHandler
 * @package Mix\Log\Handler
 */
class StreamHandler extends \Monolog\Handler\StreamHandler
{

    /**
     * {@inheritDoc}
     */
    protected function getDefaultFormatter(): FormatterInterface
    {
        return new LineFormatter("[%datetime%] %channel%.%level_name%: %message%\n", 'Y-m-d H:i:s', true);
    }

}
