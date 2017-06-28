<?php

/**
 * Response类
 * @author 刘健 <code.liu@qq.com>
 */

namespace express\swoole;

use express\base\Object;

class Response extends Object
{

    private $responder;
    // 格式值
    const FORMAT_JSON  = 0;
    const FORMAT_JSONP = 1;
    const FORMAT_XML   = 2;
    // 输出格式
    public $format = FORMAT_JSON;
    // 内容
    private $content;

    public function setResponder($responder)
    {
        $this->responder = $responder;
        return $this;
    }

    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }

    public function setHeader($key, $value)
    {
        $this->responder->header($key, $value);
    }

    // 设置HTTP状态码
    public function setStatusCode($code)
    {
        $this->responder->status($code);
        return $this;
    }

    // 输出
    public function send()
    {
        if (is_array($content)) {
            switch ($this->format) {
                case self::FORMAT_JSON:
                    $this->setHeader('Content-Type', 'application/json;charset=utf-8');
                    $content = \express\web\Json::encode($content);
                    break;
                case self::FORMAT_JSONP:
                    $this->setHeader('Content-Type', 'application/json;charset=utf-8');
                    $content = \express\web\Jsonp::encode($content);
                    break;
                case self::FORMAT_XML:
                    $this->setHeader('Content-Type', 'text/xml;charset=utf-8');
                    $content = \express\web\Xml::encode($content);
                    break;
                default:
                    $this->setHeader('Content-Type', 'application/json;charset=utf-8');
                    $content = \express\web\Json::encode($content);
                    break;
            }
        }
        if (is_scalar($content)) {
            $this->responder->end($content);
        }
    }

}
