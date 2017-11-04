<?php

namespace mix\swoole;

/**
 * redis长连接组件
 * @author 刘健 <coder.liu@qq.com>
 *
 * @method set($key, $value)
 */
class RedisPersistent extends \mix\nosql\Redis
{

    // 连接持续时间
    public $persistentTime = 7200;

    // 连接时间
    protected $_connectTime;

    // 初始化事件
    public function onInitialize()
    {
        parent::onInitialize();
        // 共用连接对象
        $this->_redis       = &\Mix::$container['_redis'];
        $this->_connectTime = &\Mix::$container['_redisConnectTime'];
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
