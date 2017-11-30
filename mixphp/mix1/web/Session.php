<?php

namespace mix\web;

use mix\base\Component;

/**
 * Session组件
 * @author 刘健 <coder.liu@qq.com>
 */
class Session extends Component
{

    // 处理者
    public $handler;
    // 有效期
    public $expires = 7200;
    // session名
    public $name = 'MIXSSID';
    // SessionKey
    protected $_sessionKey;
    // SessionID
    protected $_sessionId;

    // 请求开始事件
    public function onRequestStart()
    {
        parent::onRequestStart();
        // 载入session_id
        $this->loadSessionId();
    }

    // 请求结束事件
    public function onRequestEnd()
    {
        parent::onRequestEnd();
        // 关闭连接
        $this->handler->disconnect();
    }

    // 载入session_id
    protected function loadSessionId()
    {
        $this->_sessionId = \Mix::app()->request->cookie($this->name);
        if (is_null($this->_sessionId)) {
            $this->_sessionId = self::createSessionId();
        }
        \Mix::app()->response->setCookie($this->name, $this->_sessionId, time() + $this->expires);
        $this->_sessionKey = $this->handler->prefix . $this->_sessionId;
    }

    // 创建session_id
    protected static function createSessionId()
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
        $name  = '';
        for ($i = 0; $i < 26; $i++) {
            $name .= $chars{mt_rand(0, 61)};
        }
        return $name;
    }

    // 赋值
    public function set($name, $value)
    {
        $success = $this->handler->hMset($this->_sessionKey, [$name => serialize($value)]);
        $this->handler->setTimeout($this->_sessionKey, $this->expires);
        return $success ? true : false;
    }

    // 取值
    public function get($name = null)
    {
        if (is_null($name)) {
            $array = $this->handler->hGetAll($this->_sessionKey);
            foreach ($array as $key => $item) {
                $array[$key] = unserialize($item);
            }
            return $array ?: [];
        }
        $reslut = $this->handler->hmGet($this->_sessionKey, [$name]);
        $value  = array_shift($reslut);
        return $value === false ? null : unserialize($value);
    }

    // 判断是否存在
    public function has($name)
    {
        $exist = $this->handler->hExists($this->_sessionKey, $name);
        return $exist ? true : false;
    }

    // 删除
    public function delete($name)
    {
        $success = $this->handler->hDel($this->_sessionKey, $name);
        return $success ? true : false;
    }

    // 清除session
    public function clear()
    {
        $success = $this->handler->del($this->_sessionKey);
        return $success ? true : false;
    }

    // 获取SessionId
    public function getSessionId()
    {
        return $this->_sessionId;
    }

}
