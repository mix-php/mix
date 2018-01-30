<?php

namespace mix\async;

use mix\base\BaseObject;

/**
 * Redis异步类
 * @author 刘健 <coder.liu@qq.com>
 */
class Redis extends BaseObject
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

    // 初始化事件
    public function onInitialize()
    {
        parent::onInitialize();
        // 建立对象
        $this->_redis = new \Swoole\Redis();
    }

    // 连接
    public function connect($closure)
    {
        $database = $this->database;
        $password = $this->password;
        $this->_redis->connect($this->host, $this->port, function (\Swoole\Redis $client, $result) use ($closure, $database, $password) {
            $client->select($database, function (\Swoole\Redis $client, $result) use ($closure, $password) {
                if ($result) {
                    if ($password == '') {
                        $closure($client, $result);
                    } else {
                        $client->auth($password, function (\Swoole\Redis $client, $result) use ($closure) {
                            if ($result) {
                                $closure($client, $result);
                            }
                        });
                    }
                }
            });
        });
    }

    // 注册事件回调函数，必须在 connect 前被调用
    public function on($event, $closure)
    {
        switch ($event) {
            case 'Message':
                $this->_redis->on('message', $closure);
                break;
            case 'Close':
                $this->_redis->on('close', $closure);
                break;
        }
    }

    // 关闭连接
    public function close()
    {
        $this->_redis->close();
    }

}
