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
    // 数据库
    public $database = '';
    // 密码
    public $password = '';
    // redis对象
    protected $_redis;

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

    // 关闭连接
    public function close()
    {
        $this->_redis = null;
    }

    // 执行命令
    public function __call($name, $arguments)
    {
        $returnVal = call_user_func_array([$this->_redis, $name], $arguments);
        if ($returnVal === false) {
            throw new \RedisException('Redis执行命令出错');
        }
        return $returnVal;
    }

}
