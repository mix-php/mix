<?php

/**
 * Error类
 * @author 刘健 <code.liu@qq.com>
 */

namespace express\base;

class Error
{

    // 注册异常处理
    public static function register()
    {
        error_reporting(E_ALL);
        set_error_handler([__CLASS__, 'appError']);
        set_exception_handler([__CLASS__, 'appException']);
        register_shutdown_function([__CLASS__, 'appShutdown']);
    }

    // Error Handler
    public static function appError($errno, $errstr, $errfile = '', $errline = 0, $errcontext = [])
    {
        throw new \sys\exception\ErrorException($errno, $errstr, $errfile, $errline);
    }

    // Error Handler
    public static function appShutdown()
    {
        if ($error = error_get_last()) {
            self::appException(new \sys\exception\ErrorException($error['type'], $error['message'], $error['file'], $error['line']));
        }
    }

    // Exception Handler
    public static function appException($e)
    {
        // 获取配置
        $appDebug = Config::get('main.app_debug');
        // 清空无法接管的系统错误
        //ob_clean();

        // http异常处理
        if ($e instanceof \sys\exception\HttpException) {
            $httpExceptionTemplate = Config::get('main.http_exception');
            $data['message'] = [$e->getStatusCode() . ' / ' . $e->getMessage()];
            if ($appDebug) {
                $data['file'] = $e->getFile();
                $data['line'] = $e->getLine();
                $data['trace'] = $e->getTraceAsString();
            }
            $statusCode = $e->getStatusCode();
            $template = $httpExceptionTemplate[$statusCode];
            if (!empty($template)) {
                if (is_array($template)) {
                    switch (Config::get('main.response.array_default_convert')) {
                        case 'json':
                            $body = \sys\web\Json::create($template);
                            break;
                        case 'jsonp':
                            $body = \sys\web\Jsonp::create($template);
                            break;
                        case 'xml':
                            $body = \sys\web\Xml::create($template);
                            break;
                        default:
                            $body = \sys\web\Json::create($template);
                            break;
                    }
                } else {
                    if (!\sys\web\View::has($template)) {
                        self::appException(new \sys\exception\ViewException('视图文件不存在', $template));
                        return;
                    }
                    $body = \sys\web\View::create($template, $data);
                }
            } else {
                $body = \sys\web\Error::create($data);
            }
            $response = \sys\web\Response::instance()->setBody($body);
            $response->code($statusCode);
            $response->send();
        }

        // 其他异常处理
        $data['code'] = 500;
        if (!$appDebug) {
            $data['message'] = ['500 / 服务器内部错误'];
        } else if ($e instanceof \sys\exception\ErrorException) {
            $data['message'] = ['系统错误', $e->getMessage()];
        } else if ($e instanceof \sys\exception\RouteException) {
            $data['code'] = 404;
            $data['message'] = ['路由错误', $e->getMessage() . ':' . $e->getLocation()];
        } else if ($e instanceof \sys\exception\ConfigException) {
            $data['message'] = ['配置错误', $e->getMessage() . ':' . $e->getLocation()];
        } else if ($e instanceof \sys\exception\ViewException) {
            $data['message'] = ['视图错误', $e->getMessage() . ':' . $e->getLocation()];
        } else if ($e instanceof \sys\exception\TemplateException) {
            $data['message'] = ['模板错误', $e->getMessage() . ':' . $e->getLocation()];
        } else if ($e instanceof \PDOException) {
            $data['message'] = ['PDO错误', $e->getMessage()];
            '' == ($sql = \sys\Pdo::getLastSql()) or $data['message'][] = $sql;
        } else {
            $data['message'] = ['未定义错误', $e->getMessage()];
        }
        if ($appDebug) {
            $data['file'] = $e->getFile();
            $data['line'] = $e->getLine();
            $data['trace'] = $e->getTraceAsString();
        }
        $error = \sys\web\Error::create($data);
        $response = \sys\web\Response::instance()->setBody($error);
        $response->code($data['code']);
        $response->send();
    }

}
