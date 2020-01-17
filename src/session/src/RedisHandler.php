<?php

namespace Mix\Session;

use Mix\Bean\BeanInjector;
use Mix\Pool\ConnectionPoolInterface;
use Mix\Redis\ConnectionInterface;

/**
 * Class RedisHandler
 * @package Mix\Session
 * @author liu,jian <coder.keda@gmail.com>
 */
class RedisHandler implements SessionHandlerInterface
{

    /**
     * 连接池
     * @var ConnectionPoolInterface
     */
    public $pool;

    /**
     * 连接
     * @var ConnectionInterface
     */
    public $connection;

    /**
     * Key前缀
     * @var string
     */
    public $keyPrefix = 'SESSION:';

    /**
     * session_id
     * @var string
     */
    protected $sessionId = '';

    /**
     * Authorization constructor.
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        BeanInjector::inject($this, $config);
    }

    /**
     * 获取连接
     * @return ConnectionInterface
     */
    protected function getConnection()
    {
        return $this->pool ? $this->pool->getConnection() : $this->connection;
    }

    /**
     * 释放连接
     * @param $connection
     * @return bool
     */
    protected function release($connection)
    {
        if (!method_exists($connection, 'release')) {
            return false;
        }
        return call_user_func([$connection, 'release']);
    }

    /**
     * 设置session_id
     * @param string $sessionId
     * @return static
     */
    public function withSessionId(string $sessionId)
    {
        $this->sessionId = $sessionId;
        return $this;
    }

    /**
     * 获取session_id
     * @return string
     */
    public function getSessionId()
    {
        return $this->sessionId;
    }

    /**
     * 获取保存的key
     * @param string $sessionId
     * @return string
     */
    public function getSaveKey(string $sessionId)
    {
        return $this->keyPrefix . $sessionId;
    }

    /**
     * 判断 session_id 是否存在
     * @param string $sessionId
     * @return bool
     */
    public function exists(string $sessionId)
    {
        $key        = $this->getSaveKey($sessionId);
        $connection = $this->getConnection();
        $success    = $connection->exists($key);
        $this->release($connection);
        return $success ? true : false;
    }

    /**
     * 更新生存时间
     * @param int $maxLifetime
     * @return bool
     */
    public function expire(int $maxLifetime)
    {
        $key        = $this->getSaveKey($this->getSessionId());
        $connection = $this->getConnection();
        $success    = $connection->expire($key, $maxLifetime);
        $this->release($connection);
        return $success ? true : false;
    }

    /**
     * 赋值
     * @param string $name
     * @param $value
     * @return bool
     */
    public function set(string $name, $value)
    {
        $key        = $this->getSaveKey($this->getSessionId());
        $connection = $this->getConnection();
        $success    = $connection->hMset($key, [$name => serialize($value)]);
        $this->release($connection);
        return $success ? true : false;
    }

    /**
     * 取值
     * @param string $name
     * @param null $default
     * @return mixed
     */
    public function get(string $name, $default = null)
    {
        $key        = $this->getSaveKey($this->getSessionId());
        $connection = $this->getConnection();
        $value      = $connection->hGet($key, $name);
        $this->release($connection);
        return $value === false ? $default : unserialize($value);
    }

    /**
     * 取所有值
     * @return array
     */
    public function getAttributes()
    {
        $key        = $this->getSaveKey($this->getSessionId());
        $connection = $this->getConnection();
        $result     = $connection->hGetAll($key);
        $this->release($connection);
        foreach ($result as $name => $item) {
            $result[$name] = unserialize($item);
        }
        return $result ?: [];
    }

    /**
     * 删除
     * @param string $name
     * @return bool
     */
    public function delete(string $name)
    {
        $key        = $this->getSaveKey($this->getSessionId());
        $connection = $this->getConnection();
        $success    = $connection->hDel($key, $name);
        $this->release($connection);
        return $success ? true : false;
    }

    /**
     * 清除session
     * @return bool
     */
    public function clear()
    {
        $key        = $this->getSaveKey($this->getSessionId());
        $connection = $this->getConnection();
        $success    = $connection->del($key);
        $this->release($connection);
        return $success ? true : false;
    }

    /**
     * 判断是否存在
     * @param string $name
     * @return bool
     */
    public function has(string $name)
    {
        $key        = $this->getSaveKey($this->getSessionId());
        $connection = $this->getConnection();
        $exist      = $connection->hExists($key, $name);
        $this->release($connection);
        return $exist ? true : false;
    }

}
