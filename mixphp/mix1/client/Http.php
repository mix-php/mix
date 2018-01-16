<?php

namespace mix\client;

use mix\base\BaseObject;

/**
 * Http类
 * @author 刘健 <coder.liu@qq.com>
 */
class Http extends BaseObject
{

    // 超时时间
    public $timeout;

    // 请求头
    public $headers;

    // 上次请求的返回包体
    protected $_body;

    // 上次请求的返回的Http状态码
    protected $_statusCode;

    // 上次请求的错误信息
    protected $_error;

    // GET请求
    public function get($url)
    {
        $this->execute($url, 'GET');
        return $this;
    }

    // POST请求
    public function post($url, $requestBody)
    {
        $this->execute($url, 'POST', $requestBody);
        return $this;
    }

    // 执行
    protected function execute($url, $requestType, $requestBody = null)
    {
        // 转换请求包体
        if (is_array($requestBody)) {
            $requestBody = http_build_query($requestBody);
        }
        // 构造请求
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $requestType);
        isset($this->timeout) and curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_HTTPHEADER, isset($this->headers) ? $this->headers : []);
        isset($requestBody) and curl_setopt($ch, CURLOPT_POSTFIELDS, $requestBody);
        // 忽略SSL
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        // 返回数据
        $this->_body       = curl_exec($ch);
        $this->_error      = curl_error($ch);
        $this->_statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        // 关闭请求
        curl_close($ch);
    }

    // 返回上次请求的包体
    public function getBody()
    {
        return $this->_body;
    }

    // 返回上次请求的Http状态码
    public function getStatusCode()
    {
        return $this->_statusCode;
    }

    // 返回上次请求的错误信息
    public function getError()
    {
        return $this->_error;
    }

}
