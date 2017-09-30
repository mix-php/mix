<?php

namespace mix\nosql;

use mix\base\Component;

/**
 * redis组件
 * @author 刘健 <coder.liu@qq.com>
 *
 * @method set($key, $value)
 */
class Redis extends Component
{

    // 主机
    public $host = '';
    // 端口
    public $port = '';
    // 密码
    public $password = '';
    // 数据库
    public $database = '';

    // redis对象
    protected $_redis;

    // 请求开始事件
    public function onRequestStart()
    {
        $this->connect();
    }

    // 请求结束事件
    public function onRequestEnd()
    {
        $this->_redis = null;
    }

    // 连接
    public function connect()
    {
        $redis = new \Redis();
        // connect 这里如果设置timeout，是全局有效的，执行brPop时会受影响
        if (!$redis->connect($this->host, $this->port)) {
            throw new \Exception('Redis连接失败');
        }
        $redis->auth($this->password);
        $redis->select($this->database);
        $this->_redis = $redis;
    }

    /**
     * 执行命令
     * @author 刘健 <coder.liu@qq.com>
     */
    public function __call($name, $arguments)
    {
        $returnVal = call_user_func_array([$this->_redis, $name], $arguments);
        if ($returnVal === false) {
            throw new \RedisException('执行命令出错');
        }
        return $returnVal;
    }

}
