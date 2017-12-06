<?php

namespace mix\client;

use mix\base\Component;

/**
 * BaseRedis组件
 * @author 刘健 <coder.liu@qq.com>
 */
class BaseRedis extends Component
{

    // 主机
    public $host = '';
    // 端口
    public $port = '';
    // 数据库
    public $database = '';
    // 密码
    public $password = '';
    // redis对象
    protected $_redis;

    // 连接
    protected function connect()
    {
        $redis = new \Redis();
        // connect 这里如果设置timeout，是全局有效的，执行brPop时会受影响
        if (!$redis->connect($this->host, $this->port)) {
            throw new \RedisException('Redis连接失败');
        }
        $redis->auth($this->password);
        $redis->select($this->database);
        $this->_redis = $redis;
    }

    // 关闭连接
    public function disconnect()
    {
        $this->_redis = null;
    }

    // 自动连接
    protected function autoConnect()
    {
        if (!isset($this->_redis)) {
            $this->connect();
        }
    }

    // 执行命令
    public function __call($name, $arguments)
    {
        // 自动连接
        $this->autoConnect();
        // 执行命令
        return call_user_func_array([$this->_redis, $name], $arguments);
    }

}
