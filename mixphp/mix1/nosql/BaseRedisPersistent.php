<?php

namespace mix\nosql;

/**
 * BaseRedisPersistent组件
 * @author 刘健 <coder.liu@qq.com>
 *
 * @method set($key, $value)
 */
class BaseRedisPersistent extends BaseRedis
{

    // 连接持续时间
    public $persistentTime = 7200;
    // 连接时间
    protected $_connectTime;

    // 初始化
    public function initialize()
    {
        // 共用连接对象
        $hash               = md5($this->host . $this->port . $this->database . $this->password);
        $this->_redis       = &\Mix::$container['redis_' . $hash];
        $this->_connectTime = &\Mix::$container['redisConnectTime_' . $hash];
        // 连接
        $this->connect();
    }

    // 连接
    public function connect()
    {
        $this->close();
        $this->_connectTime = time();
        parent::connect();
    }

    // 执行命令
    public function __call($name, $arguments)
    {
        // 主动重新连接
        if ($this->_connectTime + $this->persistentTime < time()) {
            $this->connect();
        }
        try {
            // 执行命令
            return parent::__call($name, $arguments);
        } catch (\Exception $e) {
            // 长连接超时处理
            $this->connect();
            throw $e;
        }
    }

}
