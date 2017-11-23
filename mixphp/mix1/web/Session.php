<?php

namespace mix\web;

use mix\base\Component;

/**
 * Session组件
 * @author 刘健 <coder.liu@qq.com>
 */
class Session extends Component
{

    // 处理者值
    const HANDLER_REDIS = 'redis';
    // 处理者
    public $saveHandler = self::HANDLER_REDIS;
    // 处理者配置信息
    public $handlerConfig = [
        // 主机
        'host'     => '127.0.0.1',
        // 端口
        'port'     => 6379,
        // 数据库
        'database' => 0,
        // 密码
        'password' => '',
        // Key前缀
        'prefix'   => 'MIXTKID:',
    ];
    // 有效期
    public $expires = 7200;
    // session名
    public $name = 'MIXSSID';
    // 处理者
    protected $_handler;
    // Session在处理者内的key
    protected $_handlerKey;
    // SessionID
    protected $_sessionId;

    // 初始化事件
    public function onInitialize()
    {
        parent::onInitialize();
        // 创建 Handler
        $this->createHandler();
    }

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
        $this->_handler->close();
    }

    // 创建 Handler
    protected function createHandler()
    {
        switch ($this->saveHandler) {
            case self::HANDLER_REDIS:
                $this->createRedisHandler();
                break;
        }
    }

    // 创建 Redis Handler
    protected function createRedisHandler()
    {
        $redis          = new \mix\nosql\Redis([
            'host'     => $this->handlerConfig['host'],
            'port'     => $this->handlerConfig['port'],
            'database' => $this->handlerConfig['database'],
            'password' => $this->handlerConfig['password'],
        ]);
        $this->_handler = $redis;
    }

    // 载入session_id
    protected function loadSessionId()
    {
        $this->_sessionId = \Mix::app()->request->cookie($this->name);
        if (is_null($this->_sessionId)) {
            $this->_sessionId = self::createSessionId();
        }
        \Mix::app()->response->setCookie($this->name, $this->_sessionId, time() + $this->expires);
        $this->_handlerKey = $this->handlerConfig['prefix'] . $this->_sessionId;
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

    // 取值
    public function get($name = null)
    {
        if (is_null($name)) {
            $array = $this->_handler->hGetAll($this->_handlerKey);
            foreach ($array as $key => $item) {
                $array[$key] = unserialize($item);
            }
            return $array ?: [];
        }
        $reslut = $this->_handler->hmGet($this->_handlerKey, [$name]);
        $value  = array_shift($reslut);
        return $value === false ? null : unserialize($value);
    }

    // 赋值
    public function set($name, $value)
    {
        $success = $this->_handler->hMset($this->_handlerKey, [$name => serialize($value)]);
        $this->_handler->setTimeout($this->_handlerKey, $this->gcMaxLifetime);
        return $success ? true : false;
    }

    // 判断是否存在
    public function has($name)
    {
        $exist = $this->_handler->hExists($this->_handlerKey, $name);
        return $exist ? true : false;
    }

    // 删除
    public function delete($name)
    {
        $success = $this->_handler->hDel($this->_handlerKey, $name);
        return $success ? true : false;
    }

    // 清除session
    public function clear()
    {
        $success = $this->_handler->del($this->_handlerKey);
        return $success ? true : false;
    }

    // 获取SessionId
    public function getSessionId()
    {
        return $this->_sessionId;
    }

}
