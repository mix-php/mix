<?php

/**
 * Response类
 * @author 刘健 <code.liu@qq.com>
 */

namespace express\web;

use express\base\Object;

class Response extends Object
{

    // 格式值
    const FORMAT_JSON  = 0;
    const FORMAT_JSONP = 1;
    const FORMAT_XML   = 2;
    // 输出格式
    public $format = self::FORMAT_JSON;
    // json
    public $json;
    // jsonp
    public $jsonp;
    // xml
    public $xml;
    // 内容
    private $content;
    // 状态码
    public $statusCode = 200;

    // 设置内容
    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }

    // 设置Header信息
    public function setHeader($key, $value)
    {
        header("{$key}: {$value}");
        return $this;
    }

    // 重定向
    public function redirect($url)
    {
        header("Location: {$url}");
    }

    // 输出
    public function send()
    {
        $content = $this->content;
        if (is_array($content)) {
            switch ($this->format) {
                case self::FORMAT_JSON:
                    $this->setHeader('Content-Type', 'application/json;charset=utf-8');
                    $content = $this->json->encode($content);
                    break;
                case self::FORMAT_JSONP:
                    $this->setHeader('Content-Type', 'application/json;charset=utf-8');
                    $content = $this->jsonp->encode($content);
                    break;
                case self::FORMAT_XML:
                    $this->setHeader('Content-Type', 'text/xml;charset=utf-8');
                    $content = $this->xml->encode($content);
                    break;
                default:
                    $this->setHeader('Content-Type', 'application/json;charset=utf-8');
                    $content = $this->json->encode($content);
                    break;
            }
        }
        if (is_scalar($content)) {
            $this->setStatusCode();
            echo $content;
        }
    }

    // 设置HTTP状态码
    private function setStatusCode()
    {
        $status = array(
            100 => 'HTTP/1.1 100 Continue',
            101 => 'HTTP/1.1 101 Switching Protocols',
            200 => 'HTTP/1.1 200 OK',
            201 => 'HTTP/1.1 201 Created',
            202 => 'HTTP/1.1 202 Accepted',
            203 => 'HTTP/1.1 203 Non-Authoritative Information',
            204 => 'HTTP/1.1 204 No Content',
            205 => 'HTTP/1.1 205 Reset Content',
            206 => 'HTTP/1.1 206 Partial Content',
            300 => 'HTTP/1.1 300 Multiple Choices',
            301 => 'HTTP/1.1 301 Moved Permanently',
            302 => 'HTTP/1.1 302 Found',
            303 => 'HTTP/1.1 303 See Other',
            304 => 'HTTP/1.1 304 Not Modified',
            305 => 'HTTP/1.1 305 Use Proxy',
            307 => 'HTTP/1.1 307 Temporary Redirect',
            400 => 'HTTP/1.1 400 Bad Request',
            401 => 'HTTP/1.1 401 Unauthorized',
            402 => 'HTTP/1.1 402 Payment Required',
            403 => 'HTTP/1.1 403 Forbidden',
            404 => 'HTTP/1.1 404 Not Found',
            405 => 'HTTP/1.1 405 Method Not Allowed',
            406 => 'HTTP/1.1 406 Not Acceptable',
            407 => 'HTTP/1.1 407 Proxy Authentication Required',
            408 => 'HTTP/1.1 408 Request Time-out',
            409 => 'HTTP/1.1 409 Conflict',
            410 => 'HTTP/1.1 410 Gone',
            411 => 'HTTP/1.1 411 Length Required',
            412 => 'HTTP/1.1 412 Precondition Failed',
            413 => 'HTTP/1.1 413 Request Entity Too Large',
            414 => 'HTTP/1.1 414 Request-URI Too Large',
            415 => 'HTTP/1.1 415 Unsupported Media Type',
            416 => 'HTTP/1.1 416 Requested range not satisfiable',
            417 => 'HTTP/1.1 417 Expectation Failed',
            500 => 'HTTP/1.1 500 Internal Server Error',
            501 => 'HTTP/1.1 501 Not Implemented',
            502 => 'HTTP/1.1 502 Bad Gateway',
            503 => 'HTTP/1.1 503 Service Unavailable',
            504 => 'HTTP/1.1 504 Gateway Time-out',
        );
        header($status[$this->statusCode]);
    }

}
