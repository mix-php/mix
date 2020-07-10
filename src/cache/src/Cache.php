<?php

namespace Mix\Cache;

use Mix\Cache\Handler\HandlerInterface;
use Psr\SimpleCache\CacheInterface;

/**
 * Class Cache
 * @package Mix\Cache
 * @author liu,jian <coder.keda@gmail.com>
 */
class Cache implements CacheInterface
{

    /**
     * 处理器
     * @var HandlerInterface
     */
    public $handler;

    /**
     * Cache constructor.
     * @param HandlerInterface $handler
     */
    public function __construct(HandlerInterface $handler)
    {
        $this->handler = $handler;
    }

    /**
     * 获取缓存
     * @param $key
     * @param null $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return $this->handler->get($key, $default);
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
        return $this->handler->set($key, $value, $ttl);
    }

    /**
     * 删除缓存
     * @param $key
     * @return bool
     */
    public function delete($key)
    {
        return $this->handler->delete($key);
    }

    /**
     * 清除缓存
     * @return bool
     */
    public function clear()
    {
        return $this->handler->clear();
    }

    /**
     * 判断缓存是否存在
     * @param $key
     * @return bool
     */
    public function has($key)
    {
        return $this->handler->has($key);
    }

    /**
     * 批量获取
     * @param $keys
     * @param null $default
     * @return array
     */
    public function getMultiple($keys, $default = null)
    {
        $results = [];
        foreach ($keys as $key) {
            $results[$key] = $this->get($key, $default);
        }
        return $results;
    }

    /**
     * 批量设置
     * @param $values
     * @param null $ttl
     * @return bool
     */
    public function setMultiple($values, $ttl = null)
    {
        $results = [];
        foreach ($values as $key => $value) {
            $results[] = $this->set($key, $value, $ttl);
        }
        foreach ($results as $result) {
            if (!$result) {
                return false;
            }
        }
        return true;
    }

    /**
     * 批量删除
     * @param $keys
     * @return bool
     */
    public function deleteMultiple($keys)
    {
        $results = [];
        foreach ($keys as $key) {
            $results[] = $this->delete($key);
        }
        foreach ($results as $result) {
            if (!$result) {
                return false;
            }
        }
        return true;
    }

}
