<?php

namespace mix\websocket;

use mix\base\Component;

/**
 * Session组件
 * @author 刘健 <coder.liu@qq.com>
 */
class Session extends Component
{

    // 保存处理者
    public $saveHandler;
    // 保存的Key前缀
    public $saveKeyPrefix;
    // session名
    public $name = 'mixssid';
    // SessionKey
    protected $_sessionKey;
    // SessionID
    protected $_sessionId;

    // 初始化事件
    public function onInitialize()
    {
        parent::onInitialize();
        // 载入session_id
        $this->loadSessionId();
    }

    // 载入session_id
    public function loadSessionId()
    {
        // 载入session_id
        $this->_sessionId = \Mix::app()->wsRequest->get($this->name) or
        $this->_sessionId = \Mix::app()->wsRequest->cookie($this->name);
        $this->_sessionKey = $this->saveKeyPrefix . $this->_sessionId;
        // 关闭连接
        $this->saveHandler->disconnect();
    }

    // 取值
    public function get($name = null)
    {
        if (is_null($name)) {
            $array = $this->saveHandler->hGetAll($this->_sessionKey);
            foreach ($array as $key => $item) {
                $array[$key] = unserialize($item);
            }
            return $array ?: [];
        }
        $reslut = $this->saveHandler->hmGet($this->_sessionKey, [$name]);
        $value  = array_shift($reslut);
        return $value === false ? null : unserialize($value);
    }

    // 判断是否存在
    public function has($name)
    {
        $exist = $this->saveHandler->hExists($this->_sessionKey, $name);
        return $exist ? true : false;
    }

    // 获取SessionId
    public function getSessionId()
    {
        return $this->_sessionId;
    }

}
