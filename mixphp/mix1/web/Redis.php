<?php

namespace mix\web;

use mix\base\Object;

/**
 * redis 驱动
 *
 * @author 刘健 <code.liu@qq.com>
 * @method set($key, $value)
 */
class Redis extends Object
{

    // 主机
    public $host = '';
    // 端口
    public $port = '';
    // 密码
    public $password = '';
    // 数据库
    public $database = '';
    // 重连时间
    public $reconnection = 7200;

    // redis对象
    private $_redis;
    // 连接时间
    private $_connectTime;

    /**
     * 初始化
     * @author 刘健 <code.liu@qq.com>
     */
    public function init()
    {
        $this->_connectTime = time();
        isset($this->_redis) and $this->_redis = null; // 置空才会释放旧连接
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
     * @author 刘健 <code.liu@qq.com>
     */
    public function __call($name, $arguments)
    {
        // 主动重新连接
        if (\Mix::app() instanceof \mix\swoole\Application) {
            if ($this->_connectTime + $this->reconnection < time()) {
                var_dump('init');
                $this->init();
            }
        }
        try {
            // 执行命令
            $returnVal = call_user_func_array([$this->_redis, $name], $arguments);
            if ($returnVal === false) {
                throw new \RedisException('执行命令出错');
            }
            return $returnVal;
        } catch (\Exception $e) {
            // 长连接超时处理
            if (\Mix::app() instanceof \mix\swoole\Application) {
                $this->init();
            }
            throw $e;
        }
    }

}
