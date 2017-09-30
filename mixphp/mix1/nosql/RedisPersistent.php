<?php

namespace mix\nosql;

/**
 * redis长连接组件
 * @author 刘健 <coder.liu@qq.com>
 *
 * @method set($key, $value)
 */
class RedisPersistent extends Redis
{

    // 重连时间
    public $reconnection = 7200;

    // 连接时间
    protected $_connectTime;

    // 初始化事件
    public function onInitialize()
    {
        // 共用连接对象
        $this->_redis       = &\Mix::$container['_redis'];
        $this->_connectTime = &\Mix::$container['_redisConnectTime'];
        if (is_null($this->_redis)) {
            // 连接
            $this->connect();
        }
    }

    // 请求开始事件
    public function onRequestStart()
    {
    }

    // 请求结束事件
    public function onRequestEnd()
    {
    }

    // 连接
    public function connect()
    {
        isset($this->_redis) and $this->_redis = null; // 置空才会释放旧连接
        $this->_connectTime = time();
        parent::connect();
    }

    /**
     * 执行命令
     * @author 刘健 <coder.liu@qq.com>
     */
    public function __call($name, $arguments)
    {
        // 主动重新连接
        if ($this->_connectTime + $this->reconnection < time()) {
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
