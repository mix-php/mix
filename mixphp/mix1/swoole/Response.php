<?php

namespace mix\swoole;

use mix\base\Component;

/**
 * Response组件
 * @author 刘健 <coder.liu@qq.com>
 */
class Response extends Component
{

    // 格式值
    const FORMAT_JSON = 0;
    const FORMAT_JSONP = 1;
    const FORMAT_XML = 2;

    // 默认格式
    public $defaultContentType = 'text/html;charset=utf-8';

    // 默认输出格式
    public $defaultFormat = self::FORMAT_JSON;

    // json
    public $json;

    // jsonp
    public $jsonp;

    // xml
    public $xml;

    // 当前输出格式
    public $format;

    // 状态码
    public $statusCode;

    // 响应对象
    protected $_responder;

    // 内容
    protected $_content;

    // HTTP 响应头
    protected $_headers = [];

    // 设置响应者
    public function setResponder($responder)
    {
        $this->_responder = $responder;
        // 重置数据
        $this->format     = $this->defaultFormat;
        $this->statusCode = 200;
        $this->_content   = null;
        $this->_headers   = [];
    }

    // 设置内容
    public function setContent($content)
    {
        $this->_content = $content;
    }

    // 设置Header信息
    public function setHeader($key, $value)
    {
        $this->_headers[$key] = $value;
    }

    // 设置Cookie
    public function setCookie($name, $value = "", $expire = 0, $path = "", $domain = "", $secure = false, $httponly = false)
    {
        $this->_responder->cookie($name, $value, $expire, $path, $domain, $secure, $httponly);
    }

    // 重定向
    public function redirect($url)
    {
        $this->setHeader('Location', $url);
        $this->statusCode = 302;
    }

    // 输出
    public function send()
    {
        // 多次输出兼容处理
        if (!empty($this->_responder->end)) {
            return;
        }
        $this->_responder->end = true;
        // 输出
        $content = $this->_content;
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
            isset($this->_headers['Content-Type']) or $this->_headers['Content-Type'] = $this->defaultContentType;
            $this->setHeaders();
            $this->_responder->end($content);
        } else {
            $this->_responder->end('');
        }
    }

    // 设置HTTP状态码
    protected function setStatusCode()
    {
        $this->_responder->status($this->statusCode);
    }

    // 设置Header信息
    protected function setHeaders()
    {
        foreach ($this->_headers as $key => $value) {
            $this->_responder->header($key, $value);
        }
    }

}
