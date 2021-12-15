<?php

namespace Mix\Redis;

use Mix\ObjectPool\ObjectTrait;

/**
 * Class Driver
 * @package Mix\Redis
 */
class Driver
{

    use ObjectTrait;

    /**
     * @var string
     */
    protected $host = '';

    /**
     * @var int
     */
    protected $port = 6379;

    /**
     * @var string
     */
    protected $password = '';

    /**
     * @var int
     */
    protected $database = 0;

    /**
     * @var float
     */
    protected $timeout = 5.0;

    /**
     * @var int
     */
    protected $retryInterval = 0;

    /**
     * 读取超时
     * phpredis >= 3.1.3
     * @var int
     */
    protected $readTimeout = -1;

    /**
     * @var \Redis
     */
    protected $redis;

    /**
     * Driver constructor.
     * @param string $host
     * @param int $port
     * @param string $password
     * @param int $database
     * @param float $timeout
     * @param int $retryInterval
     * @param int $readTimeout
     * @throws \RedisException
     */
    public function __construct(string $host, int $port = 6379, string $password = '', int $database = 0, float $timeout = 5.0, int $retryInterval = 0, int $readTimeout = -1)
    {
        $this->host = $host;
        $this->port = $port;
        $this->password = $password;
        $this->database = $database;
        $this->timeout = $timeout;
        $this->retryInterval = $retryInterval;
        $this->readTimeout = $readTimeout;
        $this->connect();
    }

    /**
     * Get instance
     * @return \Redis
     */
    public function instance(): \Redis
    {
        return $this->redis;
    }

    /**
     * Connect
     * @throws \RedisException
     */
    public function connect()
    {
        $redis = new \Redis();
        $result = $redis->connect($this->host, $this->port, $this->timeout, null, $this->retryInterval);
        if ($result === false) {
            throw new \RedisException(sprintf('Redis connect failed (host: %s, port: %s) %s', $this->host, $this->port, $redis->getLastError()));
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
