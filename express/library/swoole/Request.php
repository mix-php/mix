<?php

/**
 * Request类
 * @author 刘健 <code.liu@qq.com>
 */

namespace express\swoole;

use express\base\Object;

class Request extends Object
{

    // 请求对象
    private $requester;
    // 请求数据
    private $get;
    private $post;
    private $route;

    // 设置请求对象
    public function setRequester($requester)
    {
        $this->requester = $requester;
        $this->setGet($requester->get());
        $this->setPost($requester->post());
        return $this;
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
