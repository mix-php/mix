<?php

namespace mix\swoole;

/**
 * redis 驱动
 *
 * @author 刘健 <coder.liu@qq.com>
 * @method set($key, $value)
 */
class Redis extends \mix\nosql\Redis
{

    // 重连时间
    public $reconnection = 7200;

    // 连接时间
    protected $_connectTime;

    /**
     * 初始化
     * @author 刘健 <coder.liu@qq.com>
     */
    public function init()
    {
        // 共用连接对象
        $this->_redis = &\Mix::$container['_redis'];
        $this->_connectTime = &\Mix::$container['_redisConnectTime'];
        if (is_null($this->_redis)) {
            // 连接
            $this->connect();
        }
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
