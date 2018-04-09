<?php

namespace mix\web;

use mix\base\Component;

/**
 * Request组件
 * @author 刘健 <coder.liu@qq.com>
 */
class Request extends Component
{

    // ROUTE参数
    protected $_route = [];

    // GET参数
    protected $_get = [];

    // POST参数
    protected $_post = [];

    // FILES参数
    protected $_files = [];

    // COOKIE参数
    protected $_cookie = [];

    // SERVER参数
    protected $_server = [];

    // HEADER参数
    protected $_header = [];

    // 初始化事件
    public function onInitialize()
    {
        parent::onInitialize();
        $this->_get    = $_GET;
        $this->_post   = $_POST;
        $this->_files  = $_FILES;
        $this->_cookie = $_COOKIE;
        $this->setServer();
        $this->setHeader();
    }

    // 设置ROUTE值
    public function setRoute($route)
    {
        $this->_route = $route;
    }

    // 设置SERVER值
    protected function setServer()
    {
        $this->_server = array_change_key_case($_SERVER, CASE_LOWER);
    }

    // 设置HEADER值
    protected function setHeader()
    {
        $header = [];
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $header[str_replace(' ', '-', strtolower(str_replace('_', ' ', substr($name, 5))))] = $value;
            }
        }
        $this->_header = $header;
    }

    // 提取GET值
    public function get($name = null)
    {
        return self::fetch($name, $this->_get);
    }

    // 提取POST值
    public function post($name = null)
    {
        return self::fetch($name, $this->_post);
    }

    // 提取FILES值
    public function files($name = null)
    {
        return self::fetch($name, $this->_files);
    }

    // 提取ROUTE值
    public function route($name = null)
    {
        return self::fetch($name, $this->_route);
    }

    // 提取COOKIE值
    public function cookie($name = null)
    {
        return self::fetch($name, $this->_cookie);
    }

    // 提取SERVER值
    public function server($name = null)
    {
        return self::fetch($name, $this->_server);
    }

    // 提取HEADER值
    public function header($name = null)
    {
        return self::fetch($name, $this->_header);
    }

    // 返回原始的HTTP包体
    public function getRawBody()
    {
        return file_get_contents('php://input');
    }

    // 提取数据
    protected static function fetch($name, $container)
    {
        return is_null($name) ? $container : (isset($container[$name]) ? $container[$name] : null);
    }

    // 是否为 GET 请求
    public function isGet()
    {
        return $this->server('request_method') == 'GET';
    }

    // 是否为 POST 请求
    public function isPost()
    {
        return $this->server('request_method') == 'POST';
    }

    // 是否为 PUT 请求
    public function isPut()
    {
        return $this->server('request_method') == 'PUT';
    }

    // 是否为 PATCH 请求
    public function isPatch()
    {
        return $this->server('request_method') == 'PATCH';
    }

    // 是否为 DELETE 请求
    public function isDelete()
    {
        return $this->server('request_method') == 'DELETE';
    }

    // 是否为 HEAD 请求
    public function isHead()
    {
        return $this->server('request_method') == 'HEAD';
    }

    // 是否为 OPTIONS 请求
    public function isOptions()
    {
        return $this->server('request_method') == 'OPTIONS';
    }

}
