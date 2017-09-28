<?php

namespace mix\swoole;

use mix\base\Object;

/**
 * Session类
 * @author 刘健 <coder.liu@qq.com>
 */
class Session extends Object
{

    // 处理者值
    const HANDLER_REDIS = 'redis';
    // 处理者
    public $saveHandler = self::HANDLER_REDIS;
    // 保存路径
    public $savePath = '';
    // 生存时间
    public $gcMaxLifetime = 7200;
    // session名
    public $name = 'MIX_SSID';

    // 处理者
    protected $_handler;
    // SessionID
    protected $_sessionId;

    // 初始化
    public function init()
    {
        if (!isset($this->_handler)) {
            // redis Handler
            if($this->saveHandler == self::HANDLER_REDIS){
                // 解析参数
                $savePath = parse_url($this->savePath);
                parse_str($savePath['query'], $query);
                $savePath += $query;
                // 创建Handler

            }
        }
    }

    // 取值
    public function get($name = null)
    {
        if (is_null($name)) {
            return $_SESSION;
        }
        return isset($_SESSION[$name]) ? $_SESSION[$name] : null;
    }

    // 赋值
    public function set($name, $value)
    {
        $_SESSION[$name] = $value;
    }

    // 判断是否存在
    public function has($name)
    {
        return isset($_SESSION[$name]);
    }

    // 删除
    public function delete($name)
    {
        unset($_SESSION[$name]);
    }

    // 清除session
    public function clear()
    {
        session_unset();
        session_destroy();
    }

}
