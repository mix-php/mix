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
        ob_clean();
        $statusCode = $e->getCode() == 404 ? 404 : 500;
        if (MIX_DEBUG) {
            $data = [
                'code'    => $statusCode,
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'type'    => get_class($e),
                'trace'   => $e->getTraceAsString(),
            ];
        } else {
            if ($statusCode == 404) {
                $data = [
                    'code'    => 404,
                    'message' => $e->getMessage(),
                ];
            }
            if ($statusCode == 500) {
                $data = [
                    'code'    => 500,
                    'message' => '服务器内部错误',
                ];
            }
        }
        $tpl = [
            404 => "error.{$this->format}.not_found",
            500 => "error.{$this->format}.internal_server_error",
        ];
        $content = (new View())->import($tpl[$statusCode], $data);
        \Mix::$app->response->statusCode = $statusCode;
        \Mix::$app->response->setContent($content);
        switch ($this->format) {
            case self::FORMAT_JSON:
                \Mix::$app->response->setHeader('Content-Type', 'application/json;charset=utf-8');
                break;
            case self::FORMAT_XML:
                \Mix::$app->response->setHeader('Content-Type', 'text/xml;charset=utf-8');
                break;
        }
        \Mix::$app->response->send();
    }

}
