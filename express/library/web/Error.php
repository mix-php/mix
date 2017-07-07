<?php

/**
 * Error类
 * @author 刘健 <code.liu@qq.com>
 */

namespace express\web;

use express\web\View;

class Error
{

    // 格式值
    const FORMAT_VIEW = 'view';
    const FORMAT_JSON = 'json';
    // 输出格式
    public $format = self::FORMAT_VIEW;
    // view
    public $view;
    // json
    public $json;

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
        throw new \express\exception\ErrorException($errno, $errstr, $errfile, $errline);
    }

    // Error Handler
    public function appShutdown()
    {
        if ($error = error_get_last()) {
            self::appException(new \express\exception\ErrorException($error['type'], $error['message'], $error['file'], $error['line']));
        }
    }

    // Exception Handler
    public function appException($e)
    {
        $data = [
            'code'    => $e->getCode(),
            'message' => $e->getMessage(),
            'file'    => $e->getFile(),
            'line'    => $e->getLine(),
            'trace'   => $e->getTraceAsString(),
        ];
        $view    = new View();
        $content = '';
        switch ($this->format) {
            case self::FORMAT_VIEW:
                $content = $view->import($this->view[$e->getCode()], $data);
                break;
            case self::FORMAT_JSON:
                $content = $view->import($this->json[$e->getCode()], $data);
                break;
        }
        \Express::$app->response->statusCode = $e->getCode();
        \Express::$app->response->setContent($content)->send();
    }

}
