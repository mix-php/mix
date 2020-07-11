<?php

namespace Mix\Cache\Handler;

/**
 * Class ArrayHandler
 * @package Mix\Cache\Handler
 */
class ArrayHandler implements HandlerInterface
{

    /**
     * @var array
     */
    protected $storage = [];

    /**
     * 获取缓存
     * @param $key
     * @param null $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        $data = $this->storage[$key] ?? null;
        if (empty($data)) {
            return $default;
        }
        list($value, $expire) = $data;
        if ($expire > 0 && $expire < time()) {
            $this->delete($key);
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
        $expire              = is_null($ttl) ? 0 : time() + $ttl;
        $data                = [
            $value,
            $expire,
        ];
        $this->storage[$key] = $data;
        return true;
    }

    /**
     * 删除缓存
     * @param $key
     * @return bool
     */
    public function delete($key)
    {
        unset($this->storage[$key]);
        return true;
    }

    /**
     * 清除缓存
     * @return bool
     */
    public function clear()
    {
        $this->storage = [];
        return true;
    }

    /**
     * 判断缓存是否存在
     * @param $key
     * @return bool
     */
    public function has($key)
    {
        return array_key_exists($key, $this->storage);
    }

}
