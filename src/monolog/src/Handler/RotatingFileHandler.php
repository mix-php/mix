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
function unlink($filename, $context = null)
{
    return @\unlink($filename, $context);
}

/**
 * Class RotatingFileHandler
 * @package Mix\Log\Handler
 */
class RotatingFileHandler extends \Monolog\Handler\RotatingFileHandler
{

    /**
     * {@inheritDoc}
     */
    protected function getDefaultFormatter(): FormatterInterface
    {
        return new LineFormatter("[%datetime%] %channel%.%level_name%: %message%\n", 'Y-m-d H:i:s', true);
    }
    
}
