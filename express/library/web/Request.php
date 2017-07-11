<?php

/**
 * Request类
 * @author 刘健 <code.liu@qq.com>
 */

namespace express\web;

use express\base\Object;

class Request extends Object
{

    // 请求数据
    private $get;
    private $post;
    private $route;

    // 初始化
    public function init()
    {
        $this->setGet($_GET);
        $this->setPost($_POST);
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

    // 设置Route值
    public function setRoute($route)
    {
        $this->route = $route;
    }

    // 获取GET值
    public function get($name = null)
    {
        return is_null($name) ? $this->get : $this->get[$name];
    }

    // 获取POST值
    public function post($name = null)
    {
        return is_null($name) ? $this->post : $this->post[$name];
    }

    // 获取Route值
    public function route($name = null)
    {
        return is_null($name) ? $this->route : $this->route[$name];
    }

}
