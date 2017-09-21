<?php

/**
 * Response类
 * @author 刘健 <code.liu@qq.com>
 */

namespace mix\swoole;

use mix\base\Object;

class Response extends Object
{

    // 响应对象
    private $responder;

    // 格式值
    const FORMAT_JSON = 0;
    const FORMAT_JSONP = 1;
    const FORMAT_XML = 2;

    // 输出格式
    public $format = self::FORMAT_JSON;

    // json
    public $json;

    // jsonp
    public $jsonp;

    // xml
    public $xml;

    // 状态码
    public $statusCode = 200;

    // 内容
    private $content;

    // 设置响应者
    public function setResponder($responder)
    {
        $this->responder = $responder;
        return $this;
    }

    // 设置内容
    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }

    // 设置Header信息
    public function setHeader($key, $value)
    {
        $this->responder->header($key, $value);
        return $this;
    }

    // 重定向
    public function redirect($url)
    {
        $this->setHeader('Location', $url);
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
            $this->responder->end($content);
        }
    }

    // 设置HTTP状态码
    private function setStatusCode()
    {
        $this->responder->status($this->statusCode);
    }

}
