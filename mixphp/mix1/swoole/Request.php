<?php

/**
 * Request类
 * @author 刘健 <coder.liu@qq.com>
 */

namespace mix\swoole;

use mix\base\Object;

class Request extends Object
{

    // ROUTE参数
    protected $_route = [];

    // 请求对象
    protected $_requester;

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

    // 设置请求对象
    public function setRequester($requester)
    {
        $this->_requester = $requester;
        // 重设请求数据
        $this->setRoute([]);
        $this->_get    = isset($requester->get) ? $requester->get : [];
        $this->_post   = isset($requester->post) ? $requester->post : [];
        $this->_files  = isset($requester->files) ? $requester->files : [];
        $this->_cookie = isset($requester->cookie) ? $requester->cookie : [];
        $this->_server = isset($requester->server) ? $requester->server : [];
        $this->_header = isset($requester->header) ? $requester->header : [];
        return $this;
    }

    // 设置ROUTE值
    public function setRoute($route)
    {
        $this->_route = $route;
    }

    // 提取GET值
    public function get($name = null)
    {
        return $this->fetch($name, $this->_get);
    }

    // 提取POST值
    public function post($name = null)
    {
        return $this->fetch($name, $this->_post);
    }

    // 提取FILES值
    public function files($name = null)
    {
        return $this->fetch($name, $this->_files);
    }

    // 提取ROUTE值
    public function route($name = null)
    {
        return $this->fetch($name, $this->_route);
    }

    // 提取COOKIE值
    public function cookie($name = null)
    {
        return $this->fetch($name, $this->_cookie);
    }

    // 提取SERVER值
    public function server($name = null)
    {
        return $this->fetch($name, $this->_server);
    }

    // 提取HEADER值
    public function header($name = null)
    {
        return $this->fetch($name, $this->_header);
    }

    // 提取数据
    protected function fetch($name, $container)
    {
        return is_null($name) ? $container : (isset($container[$name]) ? $container[$name] : null);
    }

}
