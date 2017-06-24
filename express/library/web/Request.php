<?php

/**
 * Request类
 * @author 刘健 <code.liu@qq.com>
 */

namespace express\web;

class Request
{

    private $get;
    private $post;
    private $route;

    public function init()
    {
        $this->get  = $_GET;
        $this->post = $_POST;
    }

    public function create($requestParams)
    {
        $object = clone $this;
        $object->setGet($requestParams['get']);
        $object->setPost($requestParams['post']);
        $object->setRoute($requestParams['route']);
        return $object;
    }

    public function setGet($get)
    {
        $this->get = $get;
    }

    public function setPost($post)
    {
        $this->post = $post;
    }

    public function setRoute($route)
    {
        $this->route = $route;
    }

    public function get()
    {
        return $this->get;
    }

    public function post()
    {
        return $this->post;
    }

    public function route()
    {
        return $this->route;
    }

}
