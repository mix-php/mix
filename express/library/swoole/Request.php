<?php

/**
 * Request类
 * @author 刘健 <code.liu@qq.com>
 */

namespace express\swoole;

use express\base\Object;

class Request extends Object
{

    private $requester
    private $get;
    private $post;
    private $route;

    public function setRequester($requester)
    {
        $this->requester = $requester;
        $this->setGet($requester->get());
        $this->setPost($requester->post());
        return $this;
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
