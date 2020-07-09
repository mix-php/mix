<?php

namespace Mix\Redis;

use Mix\Bean\BeanInjector;
use Mix\ObjectPool\ObjectTrait;

/**
 * Class Driver
 * @package Mix\Redis
 */
class Driver
{
    
    use ObjectTrait;

    /**
     * 主机
     * @var string
     */
    public $host = '';

    /**
     * 端口
     * @var int
     */
    public $port = 6379;

    /**
     * 密码
     * @var string
     */
    public $password = '';

    /**
     * 数据库
     * @var int
     */
    public $database = 0;

    /**
     * 超时
     * @var float
     */
    public $timeout = 5.0;

    /**
     * 重连间隔
     * @var int
     */
    public $retryInterval = 0;

    /**
     * 读取超时
     * phpredis >= 3.1.3
     * @var int
     */
    public $readTimeout = -1;

    /**
     * @var \Redis
     */
    protected $redis;

    /**
     * Driver constructor.
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        BeanInjector::inject($this, $config);
    }

    /**
     * Get instance
     * @return \Redis
     */
    public function instance()
    {
        return $this->redis;
    }

    /**
     * Connect
     * @throws \RedisException
     */
    public function connect()
    {
        $redis  = new \Redis();
        $result = $redis->connect($this->host, $this->port, $this->timeout, null, $this->retryInterval);
        if ($result === false) {
            throw new \RedisException(sprintf('Redis connect failed (host: %s, port: %s)', $this->host, $this->port));
        }
        $redis->setOption(\Redis::OPT_READ_TIMEOUT, $this->readTimeout);
        // 假设密码是字符串 0 也能通过这个校验
        if ('' != (string)$this->password) {
            $redis->auth($this->password);
        }
        $redis->select($this->database);
        $this->redis = $redis;
    }

    /**
     * Close
     */
    public function close()
    {
        $this->redis and $this->redis->close();
    }

}
