<?php

namespace Mix\Cache;

use Mix\Bean\BeanInjector;
use Mix\Pool\ConnectionPoolInterface;
use Mix\Redis\ConnectionInterface;

/**
 * Class RedisHandler
 * @package Mix\Cache
 * @author liu,jian <coder.keda@gmail.com>
 */
class RedisHandler implements CacheHandlerInterface
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
    public $keyPrefix = 'CACHE:';

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
     * 获取缓存
     * @param $key
     * @param null $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        $cacheKey   = $this->keyPrefix . $key;
        $connection = $this->getConnection();
        $value      = $connection->get($cacheKey);
        $this->release($connection);
        if (empty($value)) {
            return $default;
        }
        $value = unserialize($value);
        if ($value === false) {
            return $default;
        }
        return $value;
    }

    /**
     * 设置缓存
     * @param $key
     * @param $value
     * @param null $ttl
     * @return bool
     */
    public function set($key, $value, $ttl = null)
    {
        $cacheKey   = $this->keyPrefix . $key;
        $connection = $this->getConnection();
        if (is_null($ttl)) {
            $success = $connection->set($cacheKey, serialize($value));
        } else {
            $success = $connection->setex($cacheKey, $ttl, serialize($value));
        }
        $this->release($connection);
        return $success ? true : false;
    }

    /**
     * 删除缓存
     * @param $key
     * @return bool
     */
    public function delete($key)
    {
        $cacheKey   = $this->keyPrefix . $key;
        $connection = $this->getConnection();
        $success    = $connection->del($cacheKey);
        $this->release($connection);
        return $success ? true : false;
    }

    /**
     * 清除缓存
     * @return bool
     */
    public function clear()
    {
        $iterator   = null;
        $connection = $this->getConnection();
        while (true) {
            $keys = $connection->scan($iterator, "{$this->keyPrefix}*");
            if ($keys === false) {
                return true;
            }
            foreach ($keys as $key) {
                $connection->del($key);
            }
        }
        $this->release($connection);
        return true;
    }

    /**
     * 判断缓存是否存在
     * @param $key
     * @return bool
     */
    public function has($key)
    {
        $cacheKey   = $this->keyPrefix . $key;
        $connection = $this->getConnection();
        $success    = $connection->exists($cacheKey);
        $this->release($connection);
        return $success ? true : false;
    }

}
