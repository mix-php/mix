<?php

namespace Mix\Log;

use Mix\Bean\BeanInjector;
use Mix\Console\CommandLine\Color;
use Psr\Log\LoggerInterface;

/**
 * Class Logger
 * @package Mix\Log
 * @author liu,jian <coder.keda@gmail.com>
 */
class Logger implements LoggerInterface
{

    /**
     * 日志记录级别
     * @var array
     */
    public $levels = ['emergency', 'alert', 'critical', 'error', 'warning', 'notice', 'info', 'debug'];

    /**
     * 处理者
     * @var \Mix\Log\LoggerHandlerInterface
     */
    public $handler;

    /**
     * Logger constructor.
     * @param array $config
     * @throws \PhpDocReader\AnnotationException
     * @throws \ReflectionException
     */
    public function __construct(array $config = [])
    {
        BeanInjector::inject($this, $config);
    }

    /**
     * emergency日志
     * @param string $message
     * @param array $context
     */
    public function emergency($message, array $context = [])
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    /**
     * alert日志
     * @param string $message
     * @param array $context
     */
    public function alert($message, array $context = [])
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    /**
     * critical日志
     * @param string $message
     * @param array $context
     */
    public function critical($message, array $context = [])
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    /**
     * error日志
     * @param string $message
     * @param array $context
     */
    public function error($message, array $context = [])
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    /**
     * warning日志
     * @param string $message
     * @param array $context
     */
    public function warning($message, array $context = [])
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    /**
     * notice日志
     * @param string $message
     * @param array $context
     */
    public function notice($message, array $context = [])
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    /**
     * info日志
     * @param string $message
     * @param array $context
     */
    public function info($message, array $context = [])
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    /**
     * debug日志
     * @param string $message
     * @param array $context
     */
    public function debug($message, array $context = [])
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    /**
     * 记录日志
     * @param mixed $level
     * @param string $message
     * @param array $context
     */
    public function log($level, $message, array $context = [])
    {
        if (in_array($level, Constants::LEVELS) && !in_array($level, $this->levels)) {
            return;
        }
        $message = static::interpolate($message, $context);
        list($msec, $sec) = explode(' ', microtime());
        $time   = date('Y-m-d H:i:s', $sec) . '.' . substr($msec, 2, 3);
        $pid    = getmypid();
        $header = "[{$level}] {$time} <{$pid}>";
        switch ($level) { // 渲染颜色
            case 'error':
                $header = Color::new(Color::FG_RED)->sprint($header);
                break;
            case 'warning':
                $header = Color::new(Color::FG_YELLOW)->sprint($header);
                break;
            case 'notice':
                $header = Color::new(Color::FG_GREEN)->sprint($header);
                break;
            case 'debug':
                $header = Color::new(Color::FG_CYAN)->sprint($header);
                break;
            case 'info':
                $header = Color::new(Color::FG_BLUE)->sprint($header);
                break;
        }
        $message = "{$header} {$message}" . PHP_EOL;
        $this->handler->handle($level, $message);
    }

    /**
     * @param $message
     * @param array $context
     * @return string
     */
    protected static function interpolate($message, array $context = [])
    {
        // build a replacement array with braces around the context keys
        $replace = [];
        foreach ($context as $key => $val) {
            // check that the value can be casted to string
            if (!is_array($val) && (!is_object($val) || method_exists($val, '__toString'))) {
                $replace['{' . $key . '}'] = $val;
            }
        }
        // interpolate replacement values into the message and return
        return strtr($message, $replace);
    }

}
