<?php

namespace mix\web;

use mix\base\Component;

/**
 * Request组件基类
 * @author 刘健 <coder.liu@qq.com>
 */
class BaseRequest extends Component
{

    // ROUTE 参数
    protected $_route = [];

    // GET 参数
    protected $_get = [];

    // POST 参数
    protected $_post = [];

    // FILES 参数
    protected $_files = [];

    // COOKIE 参数
    protected $_cookie = [];

    // SERVER 参数
    protected $_server = [];

    // HEADER 参数
    protected $_header = [];

    // 设置 ROUTE 值
    public function setRoute($route)
    {
        $this->_route = $route;
    }

    // 提取 GET 值
    public function get($name = null)
    {
        return self::fetch($name, $this->_get);
    }

    // 提取 POST 值
    public function post($name = null)
    {
        return self::fetch($name, $this->_post);
    }

    // 提取 FILES 值
    public function files($name = null)
    {
        return self::fetch($name, $this->_files);
    }

    // 提取 ROUTE 值
    public function route($name = null)
    {
        return self::fetch($name, $this->_route);
    }

    // 提取 COOKIE 值
    public function cookie($name = null)
    {
        return self::fetch($name, $this->_cookie);
    }

    // 提取 SERVER 值
    public function server($name = null)
    {
        return self::fetch($name, $this->_server);
    }

    // 提取 HEADER 值
    public function header($name = null)
    {
        return self::fetch($name, $this->_header);
    }

    // 提取数据
    protected static function fetch($name, $container)
    {
        return is_null($name) ? $container : (isset($container[$name]) ? $container[$name] : null);
    }

    // 是否为 GET 请求
    public function isGet()
    {
        return $this->method() == 'GET';
    }

    // 是否为 POST 请求
    public function isPost()
    {
        return $this->method() == 'POST';
    }

    // 是否为 PUT 请求
    public function isPut()
    {
        return $this->method() == 'PUT';
    }

    // 是否为 PATCH 请求
    public function isPatch()
    {
        return $this->method() == 'PATCH';
    }

    // 是否为 DELETE 请求
    public function isDelete()
    {
        return $this->method() == 'DELETE';
    }

    // 是否为 HEAD 请求
    public function isHead()
    {
        return $this->method() == 'HEAD';
    }

    // 是否为 OPTIONS 请求
    public function isOptions()
    {
        return $this->method() == 'OPTIONS';
    }

    // 返回请求方式
    public function method()
    {
        return $this->server('request_method');
    }

    // 返回请求的路径
    public function path()
    {
        return $this->server('path_info');
    }

    // 返回请求的URL
    public function url()
    {
        return $this->header('host') . $this->path();
    }

    // 返回请求的完整URL
    public function fullUrl()
    {
        return $this->header('host') . $this->server('request_uri');
    }

}