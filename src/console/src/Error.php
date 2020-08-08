<?php

namespace Mix\Console;

use Mix\Console\Event\HandleExceptionEvent;
use Mix\Console\Exception\ErrorException;
use Mix\Console\Exception\NotFoundException;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;

/**
 * Class Error
 * @package Mix\Console
 * @author liu,jian <coder.keda@gmail.com>
 */
class Error
{

    /**
     * @var int
     */
    public $level = E_ALL;

    /**
     * @var LoggerInterface
     */
    public $logger;

    /**
     * @var EventDispatcherInterface
     */
    public $dispatcher;

    /**
     * Error constructor.
     * @param int $level
     * @param LoggerInterface $logger
     */
    public function __construct(int $level, LoggerInterface $logger)
    {
        $this->level  = $level;
        $this->logger = $logger;
        $this->register();
    }

    /**
     * 注册错误处理
     */
    public function register()
    {
        // 设置错误级别
        $level = $this->level;
        if (error_reporting() !== $level) {
            error_reporting($level);
        }
        // 注册错误处理
        set_error_handler([$this, 'appError']);
        set_exception_handler([$this, 'appException']); // swoole 不支持该函数
        register_shutdown_function([$this, 'appShutdown']);
    }

    /**
     * 错误处理
     * @param $errno
     * @param $errstr
     * @param string $errfile
     * @param int $errline
     */
    public function appError($errno, $errstr, $errfile = '', $errline = 0)
    {
        if (error_reporting() & $errno) {
            // 委托给异常处理
            if (static::isFatalWarning($errno, $errstr)) {
                $this->appException(new ErrorException($errno, $errstr, $errfile, $errline));
                return;
            }
            // 转换为异常抛出
            throw new ErrorException($errno, $errstr, $errfile, $errline);
        }
    }

    /**
     * 停止处理
     */
    public function appShutdown()
    {
        if (!is_null($error = error_get_last()) && static::isFatal($error['type'])) {
            // 委托给异常处理
            $this->appException(new ErrorException($error['type'], $error['message'], $error['file'], $error['line']));
        }
    }

    /**
     * 异常处理
     * @param $ex
     */
    public function appException($ex)
    {
        $this->handleException($ex);
    }

    /**
     * 返回错误级别
     * @param $errno
     * @return string
     */
    public static function levelType($errno)
    {
        if (static::isError($errno)) {
            return 'error';
        }
        if (static::isWarning($errno)) {
            return 'warning';
        }
        if (static::isNotice($errno)) {
            return 'notice';
        }
        return 'error';
    }

    /**
     * 是否错误类型
     * 全部类型：http://php.net/manual/zh/errorfunc.constants.php
     * @param $type
     * @return bool
     */
    public static function isError($errno)
    {
        return in_array($errno, [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR]);
    }

    /**
     * 是否警告类型
     * 全部类型：http://php.net/manual/zh/errorfunc.constants.php
     * @param $type
     * @return bool
     */
    public static function isWarning($errno)
    {
        return in_array($errno, [E_WARNING, E_CORE_WARNING, E_COMPILE_WARNING, E_USER_WARNING]);
    }

    /**
     * 是否通知类型
     * 全部类型：http://php.net/manual/zh/errorfunc.constants.php
     * @param $type
     * @return bool
     */
    public static function isNotice($errno)
    {
        return in_array($errno, [E_NOTICE, E_USER_NOTICE, E_DEPRECATED, E_USER_DEPRECATED, E_STRICT]);
    }

    /**
     * 是否为致命错误
     * @param $errno
     * @return bool
     */
    public static function isFatal($errno)
    {
        return in_array($errno, [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE]);
    }

    /**
     * 是否致命警告类型
     * 特殊的警告，出现后 try/catch 将无法捕获异常。
     * @param $errno
     * @param $errstr
     * @return bool
     */
    public static function isFatalWarning($errno, $errstr)
    {
        if ($errno == E_WARNING && strpos($errstr, 'require') === 0) {
            return true;
        }
        return false;
    }

    /**
     * 异常处理
     * @param \Throwable $ex
     */
    public function handleException(\Throwable $ex)
    {
        // 命令处理异常
        if ($ex instanceof NotFoundException) {
            println($ex->getMessage());
            return;
        }

        // 输出日志
        $this->log($ex);

        // event dispatch
        $event            = new HandleExceptionEvent();
        $event->exception = $ex;
        $this->dispatch($event);
    }

    /**
     * Dispatch
     * @param object $event
     */
    protected function dispatch(object $event)
    {
        if (!isset($this->dispatcher)) {
            return;
        }
        $this->dispatcher->dispatch($event);
    }

    /**
     * 输出日志
     * @param \Throwable $ex
     */
    protected function log(\Throwable $ex)
    {
        $logger = $this->logger;
        // 构造内容
        list($message, $context) = static::format($ex, \Mix::$app->appDebug);
        // 写入
        $level = static::levelType($context['code']);
        switch ($level) {
            case 'error':
                $logger->error($message, $context);
                break;
            case 'warning':
                $logger->warning($message, $context);
                break;
            case 'notice':
                $logger->notice($message, $context);
                break;
        }
    }

    /**
     * 格式化
     * @param \Throwable $e
     * @param bool $debug
     * @return array
     */
    public static function format(\Throwable $e, bool $debug)
    {
        $context = [
            'code'    => $e->getCode(),
            'message' => $e->getMessage(),
            'file'    => $e->getFile(),
            'line'    => $e->getLine(),
            'type'    => get_class($e),
            'trace'   => $e->getTraceAsString(),
        ];
        $trace   = explode("\n", $context['trace']);
        foreach ($trace as $key => $value) {
            if (strpos($value, '): ') !== false) {
                // 切割为数组
                $fragments   = [];
                $tmp         = explode(' ', $value);
                $fragments[] = array_shift($tmp);
                $tmp1        = explode('): ', implode(' ', $tmp));
                $tmp1[0]     .= ')';
                if (count($tmp1) == 2) {
                    // IDE 可识别处理，只有放最后才可识别
                    $fragments[]  = array_pop($tmp1);
                    $fragments[]  = array_pop($tmp1);
                    $fragments[2] = str_replace(['.php(', ')'], ['.php on line ', ''], $fragments[2]);
                    $fragments[2] = 'in ' . $fragments[2];
                    // 合并
                    $value = implode(' ', $fragments);
                }
            }
            $trace[$key] = ' ' . $value;
        }
        $context['trace'] = implode("\n", $trace);
        $message          = "{message}\n[code] {code} [type] {type}\n[file] in {file} on line {line}\n{trace}";
        if (!$debug) {
            $message = "{message} [{code}] {type} in {file} on line {line}";
        }
        return [$message, $context];
    }

}
