<?php

namespace Mix\Cache\Handler;

/**
 * Interface HandlerInterface
 * @package Mix\Cache\Handler
 * @author liu,jian <coder.keda@gmail.com>
 */
interface HandlerInterface
{

    /**
     * 获取缓存
     * @param $key
     * @param null $default
     * @return mixed
     */
    public function get($key, $default = null);

    /**
     * 设置缓存
     * @param $key
     * @param $value
     * @param null $ttl
     * @return bool
     */
    public function set($key, $value, $ttl = null);

    /**
     * 删除缓存
     * @param $key
     * @return bool
     */
    public function delete($key);

    /**
     * 清除缓存
     * @return bool
     */
    public function clear();

    /**
     * 判断缓存是否存在
     * @param $key
     * @return bool
     */
    public function has($key);

}
