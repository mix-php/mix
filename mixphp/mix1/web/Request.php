<?php

/**
 * Request类
 * @author 刘健 <code.liu@qq.com>
 */

namespace mix\web;

use mix\base\Object;

class Request extends Object
{

    // GET参数
    private $get = [];

    // POST参数
    private $post = [];

    // FILES参数
    private $files = [];

    // ROUTE参数
    private $route = [];

    // 初始化
    public function init()
    {
        $this->setGet($_GET);
        $this->setPost($_POST);
        $this->setFiles($_FILES);
    }

    // 设置ROUTE值
    public function setRoute($route)
    {
        $this->route = $route;
    }

    // 设置GET值
    public function setGet($get)
    {
        $this->get = $get;
    }

    // 设置POST值
    public function setPost($post)
    {
        $this->post = $post;
    }

    // 设置FILES值
    public function setFiles($files)
    {
        $this->files = $files;
    }

    // 提取GET值
    public function get($name = null)
    {
        return is_null($name) ? $this->get : (isset($this->get[$name]) ? $this->get[$name] : null);
    }

    // 提取POST值
    public function post($name = null)
    {
        return is_null($name) ? $this->post : (isset($this->post[$name]) ? $this->post[$name] : null);
    }

    // 提取FILES值
    public function files($name = null)
    {
        return is_null($name) ? $this->files : (isset($this->files[$name]) ? $this->files[$name] : null);
    }

    // 提取ROUTE值
    public function route($name = null)
    {
        return is_null($name) ? $this->route : (isset($this->route[$name]) ? $this->route[$name] : null);
    }

}
