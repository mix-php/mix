<?php

namespace mix\console;

use mix\base\Component;

/**
 * Error类
 * @author 刘健 <coder.liu@qq.com>
 */
class Error extends Component
{

    // 注册异常处理
    public function register()
    {
        error_reporting(E_ALL);
        set_error_handler([$this, 'appError']);
        set_exception_handler([$this, 'appException']);
        register_shutdown_function([$this, 'appShutdown']);
    }

    // Error Handler
    public function appError($errno, $errstr, $errfile = '', $errline = 0, $errcontext = [])
    {
        throw new \mix\exception\ErrorException($errno, $errstr, $errfile, $errline);
    }

    // Error Handler
    public function appShutdown()
    {
        if ($error = error_get_last()) {
            self::appException(new \mix\exception\ErrorException($error['type'], $error['message'], $error['file'], $error['line']));
        }
    }

    // Exception Handler
    public function appException($e)
    {
        // debug处理 & exit处理
        if ($e instanceof \mix\exception\DebugException || $e instanceof \mix\exception\ExitException) {
            \Mix::app()->response->setContent($e->getMessage());
            \Mix::app()->response->send();
            return;
        }
        // 错误参数定义
        $errors = [
            'code'    => $e->getCode(),
            'message' => $e->getMessage(),
            'file'    => $e->getFile(),
            'line'    => $e->getLine(),
            'type'    => get_class($e),
            'trace'   => $e->getTraceAsString(),
        ];
        // 日志处理
        if (isset(\Mix::app()->components['log']) && !($e instanceof \mix\exception\NotFoundException)) {
            $time    = date('Y-m-d H:i:s');
            $message = "[time] {$time}" . PHP_EOL;
            $message .= "[code] {$errors['code']}" . PHP_EOL;
            $message .= "[message] {$errors['message']}" . PHP_EOL;
            $message .= "[type] {$errors['type']}" . PHP_EOL;
            $message .= "[file] {$errors['file']} line {$errors['line']}" . PHP_EOL;
            $message .= "[trace] {$errors['trace']}" . PHP_EOL;
            $message .= str_replace('Array', '$_SERVER', print_r($_SERVER, true));
            $message .= str_replace('Array', '$_GET', print_r($_GET, true));
            $message .= str_replace('Array', '$_POST', print_r($_POST, true));
            $message .= PHP_EOL;
            \Mix::app()->log->error($message);
        }
        // 清空系统错误
        ob_get_contents() and ob_clean();
        // 错误响应
        echo "{$errors['message']}" . PHP_EOL;
        echo "{$errors['type']} code {$errors['code']}" . PHP_EOL;
        echo "{$errors['file']} line {$errors['line']}" . PHP_EOL;
        echo $errors['trace'] . PHP_EOL;
    }

}
