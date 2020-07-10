<?php

namespace Mix\Cache\Handler;

use Mix\Redis\Redis;

/**
 * Class RedisHandler
 * @package Mix\Cache\Handler
 * @author liu,jian <coder.keda@gmail.com>
 */
class RedisHandler implements HandlerInterface
{

    /**
     * 连接池
     * @var Redis
     */
    public $redis;

    /**
     * Key前缀
     * @var string
     */
    public $keyPrefix = 'CACHE:';

    /**
     * RedisHandler constructor.
     * @param Redis $redis
     * @param string $keyPrefix
     */
    public function __construct(Redis $redis, string $keyPrefix = 'CACHE:')
    {
        $this->redis     = $redis;
        $this->keyPrefix = $keyPrefix;
    }

    /**
     * 获取缓存
     * @param $key
     * @param null $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        $cacheKey = $this->keyPrefix . $key;
        $value    = $this->redis->get($cacheKey);
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
        $cacheKey = $this->keyPrefix . $key;
        if (is_null($ttl)) {
            $success = $this->redis->set($cacheKey, serialize($value));
        } else {
            $success = $this->redis->setex($cacheKey, $ttl, serialize($value));
        }
        return $success ? true : false;
    }

    /**
     * 删除缓存
     * @param $key
     * @return bool
     */
    public function delete($key)
    {
        $cacheKey = $this->keyPrefix . $key;
        $success  = $this->redis->del($cacheKey);
        return $success ? true : false;
    }

    /**
     * 清除缓存
     * @return bool
     */
    public function clear()
    {
        $iterator = null;
        while (true) {
            $keys = $this->redis->scan($iterator, "{$this->keyPrefix}*");
            if ($keys === false) {
                return true;
            }
            foreach ($keys as $key) {
                $this->redis->del($key);
            }
        }
        return true;
    }

    /**
     * 判断缓存是否存在
     * @param $key
     * @return bool
     */
    public function has($key)
    {
        $cacheKey = $this->keyPrefix . $key;
        $success  = $this->redis->exists($cacheKey);
        return $success ? true : false;
    }

}
