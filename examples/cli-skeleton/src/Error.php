<?php

namespace App;

use App\Container\Logger;

/**
 * Class Error
 * @package App
 */
class Error
{

    /**
     * @var Error
     */
    static private $instance;

    public static function register(): void
    {
        if (!isset(self::$instance)) {
            self::$instance = new Error();
            set_error_handler([self::$instance, 'error']);
            set_exception_handler([self::$instance, 'exception']); // swoole 协程不支持该函数
            register_shutdown_function([self::$instance, 'shutdown']);
        }
    }

    /**
     * @param $errno
     * @param $errstr
     * @param string $errfile
     * @param int $errline
     */
    public function error($errno, $errstr, $errfile = '', $errline = 0): void
    {
        if (error_reporting() & $errno) {
            // 委托给异常处理
            $this->exception(new \Error(sprintf('%s in %s on line %d', $errstr, $errfile, $errline), $errno));
        }
    }

    public function shutdown(): void
    {
        $isFatal = function ($errno) {
            return in_array($errno, [E_ERROR, E_PARSE, E_CORE_ERROR, E_CORE_WARNING, E_COMPILE_ERROR, E_COMPILE_WARNING]);
        };
        if (!is_null($error = error_get_last()) && $isFatal($error['type'])) {
            // 委托给异常处理
            $this->exception(new \Error(sprintf('%s in %s on line %d', $error['message'], $error['file'], $error['line']), $error['type']));
        }
    }

    /**
     * @param \Throwable $ex
     */
    public function exception(\Throwable $ex): void
    {
        $message = sprintf('%s in %s on line %d', $ex->getMessage(), $ex->getFile(), $ex->getLine());
        switch ($ex->getCode()) {
            case E_NOTICE:
            case E_USER_NOTICE:
                Logger::instance()->notice($message);
                break;
            case E_WARNING:
            case E_CORE_WARNING:
            case E_COMPILE_WARNING:
            case E_USER_WARNING:
                Logger::instance()->warning($message);
                break;
            default:
                Logger::instance()->error($message);
        }
    }

    /**
     * @param \Throwable $ex
     */
    public static function handle(\Throwable $ex)
    {
        self::$instance->exception($ex);
    }

}
