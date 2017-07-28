<?php

/**
 * redis 驱动
 * @author 刘健 <code.liu@qq.com>
 */

namespace mix\nosql;

use mix\base\Object;

class Redis extends Object
{

    // 配置信息
    public $host;
    public $port;
    public $password;
    public $database;
    // redis对象
    private $redis;

    /**
     * 初始化
     * @author 刘健 <code.liu@qq.com>
     */
    public function init()
    {
        $this->connect();
    }

    /**
     * 连接
     * @author 刘健 <code.liu@qq.com>
     */
    private function connect()
    {
        $redis = new \Redis();
        // connect 这里如果设置timeout，是全局有效的，执行brPop时会受影响
        if (!$redis->connect($this->host, $this->port)) {
            throw new \Exception('Redis Connect Failure');
        }
        $redis->auth($this->password);
        $redis->select($this->database);
        $this->redis = $redis;
    }

    /**
     * 执行命令
     * @author 刘健 <code.liu@qq.com>
     */
    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->redis, $name], $arguments);
    }

}
