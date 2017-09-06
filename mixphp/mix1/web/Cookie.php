<?php

/**
 * Cookie类
 * @author 刘健 <code.liu@qq.com>
 */

namespace mix\web;

use mix\base\Object;

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

    // 取值
    public function get($name = null)
    {
        if (is_null($name)) {
            return $_COOKIE;
        }
        return isset($_COOKIE[$name]) ? $_COOKIE[$name] : null;
    }

    // 赋值
    public function set($name, $value, $expire = null)
    {
        setcookie($name, $value, time() + (is_null($expire) ? $this->expire : $expire), $this->domain, $this->secure, $this->httponly);
    }

    // 判断是否存在
    public function has($name)
    {
        return isset($_COOKIE[$name]);
    }

    // 删除
    public function delete($name)
    {
        $this->set($name, null);
    }

    // 清空当前域所有cookie
    public function clear()
    {
        foreach ($_COOKIE as $name => $value) {
            $this->set($name, null);
        }
    }

}
