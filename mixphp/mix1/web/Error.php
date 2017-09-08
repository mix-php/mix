<?php

/**
 * Error类
 * @author 刘健 <code.liu@qq.com>
 */

namespace mix\web;

use mix\web\View;

class Error
{

    // 格式值
    const FORMAT_HTML = 'html';
    const FORMAT_JSON = 'json';
    const FORMAT_XML = 'xml';

    // 输出格式
    public $format = self::FORMAT_HTML;

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
        $errors = [
            'code'    => $e->getCode(),
            'message' => $e->getMessage(),
            'file'    => $e->getFile(),
            'line'    => $e->getLine(),
            'type'    => get_class($e),
            'trace'   => $e->getTraceAsString(),
        ];
        isset($e->statusCode) or $e->statusCode = 500;
        // 日志处理
        if (!is_null(\Mix::app()->log) && $e->statusCode != 404) {
            $time = date('Y-m-d H:i:s');
            $message = "[time] {$time}" . PHP_EOL;
            $message .= "[code] {$errors['code']}" . PHP_EOL;
            $message .= "[message] {$errors['message']}" . PHP_EOL;
            $message .= "[type] {$errors['type']}" . PHP_EOL;
            $message .= "[file] {$errors['file']} line {$errors['line']}" . PHP_EOL;
            $message .= "[trace] {$errors['trace']}" . PHP_EOL;
            ob_start();
            print_r($_SERVER);
            $message .= str_replace('Array', '$_SERVER', ob_get_clean());
            ob_start();
            print_r($_GET);
            $message .= str_replace('Array', '$_GET', ob_get_clean());
            ob_start();
            print_r($_POST);
            $message .= str_replace('Array', '$_POST', ob_get_clean());
            $message .= PHP_EOL;
            \Mix::app()->log->error($message);
        }
        // 错误响应
        ob_get_contents() and ob_clean();
        if (!MIX_DEBUG) {
            if ($e->statusCode == 404) {
                $errors = [
                    'code'    => 404,
                    'message' => $e->getMessage(),
                ];
            }
            if ($e->statusCode == 500) {
                $errors = [
                    'code'    => 500,
                    'message' => '服务器内部错误',
                ];
            }
        }
        $tpl = [
            404 => "error.{$this->format}.not_found",
            500 => "error.{$this->format}.internal_server_error",
        ];
        $content = (new View())->import($tpl[$e->statusCode], $errors);
        \Mix::app()->response->statusCode = $e->statusCode;
        \Mix::app()->response->setContent($content);
        switch ($this->format) {
            case self::FORMAT_JSON:
                \Mix::app()->response->setHeader('Content-Type', 'application/json;charset=utf-8');
                break;
            case self::FORMAT_XML:
                \Mix::app()->response->setHeader('Content-Type', 'text/xml;charset=utf-8');
                break;
        }
        \Mix::app()->response->send();
    }

}
