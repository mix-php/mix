<?php

namespace mix\web;

use mix\base\Object;

/**
 * Cookie类
 * @author 刘健 <code.liu@qq.com>
 */
class Cookie extends Object
{

    // 过期时间
    public $expire = 31536000;

    // 有效的服务器路径
    public $path = '/';

    // 有效域名/子域名
    public $domain = '';

    // 仅通过安全的 HTTPS 连接传给客户端
    public $secure = false;

    // 仅可通过 HTTP 协议访问
    public $httponly = false;

    // 用户Cookie
    protected $_cookie;

    // 初始化
    public function init()
    {
        $this->_cookie = \Mix::app()->request->cookie();
    }

    // 取值
    public function get($name = null)
    {
        if (is_null($name)) {
            return $this->_cookie;
        }
        return isset($this->_cookie[$name]) ? $this->_cookie[$name] : null;
    }

    // 赋值
    public function set($name, $value, $expire = null)
    {
        \Mix::app()->response->setCookie($name, $value, time() + (is_null($expire) ? $this->expire : $expire), $this->path, $this->domain, $this->secure, $this->httponly);
    }

    // 判断是否存在
    public function has($name)
    {
        return isset($this->_cookie[$name]);
    }

    // 删除
    public function delete($name)
    {
        $this->set($name, null);
    }

    // 清空当前域所有cookie
    public function clear()
    {
        foreach ($this->_cookie as $name => $value) {
            $this->set($name, null);
        }
    }

}
