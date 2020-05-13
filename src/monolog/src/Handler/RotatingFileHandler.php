<?php

namespace Monolog\Handler;

/**
 * 重写写系统方法，使其失效
 * @param callable $call
 */
if (!function_exists('Monolog\Handler\set_error_handler')) {
    function set_error_handler(callable $call)
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
