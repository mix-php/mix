<?php

namespace mix\client;

/**
 * BaseRedisPersistent组件
 * @author 刘健 <coder.liu@qq.com>
 */
class BaseRedisPersistent extends BaseRedis
{

    // 连接持续时间
    public $persistentTime = 7200;

    // 最后活动时间
    protected $_lastActiveTime;

    // 初始化
    public function initialize()
    {
        // 共用连接对象
        $hash                  = md5($this->host . $this->port . $this->database . $this->password);
        $this->_redis          = &\Mix::$container['redis_' . $hash];
        $this->_lastActiveTime = &\Mix::$container['redisLastActiveTime_' . $hash];
    }

    // 连接
    public function connect()
    {
        // 更新活动时间
        $this->_lastActiveTime = time();
        // 连接
        parent::connect();
    }

    // 重新连接
    protected function reconnect()
    {
        $this->disconnect();
        $this->connect();
    }

    // 执行命令
    public function __call($name, $arguments)
    {
        // 主动重新连接
        if (isset($this->_lastActiveTime) && ($this->_lastActiveTime + $this->persistentTime < time())) {
            $this->reconnect();
        }
        try {
            // 更新活动时间
            $this->_lastActiveTime = time();
            // 执行命令
            return parent::__call($name, $arguments);
        } catch (\Exception $e) {
            // 长连接超时处理
            $this->reconnect();
            throw $e;
        }
    }

}
