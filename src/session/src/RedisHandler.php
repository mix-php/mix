<?php

namespace Mix\Session;

use Mix\Bean\BeanInjector;
use Mix\Redis\Redis;

/**
 * Class RedisHandler
 * @package Mix\Session
 * @author liu,jian <coder.keda@gmail.com>
 */
class RedisHandler implements SessionHandlerInterface
{

    /**
     * 连接
     * @var Redis
     */
    public $redis;

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
        $key     = $this->getSaveKey($sessionId);
        $success = $this->redis->exists($key);
        return $success ? true : false;
    }

    /**
     * 更新生存时间
     * @param int $maxLifetime
     * @return bool
     */
    public function expire(int $maxLifetime)
    {
        $key     = $this->getSaveKey($this->getSessionId());
        $success = $this->redis->expire($key, $maxLifetime);
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
        $key     = $this->getSaveKey($this->getSessionId());
        $success = $this->redis->hMset($key, [$name => serialize($value)]);
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
        $key   = $this->getSaveKey($this->getSessionId());
        $value = $this->redis->hGet($key, $name);
        return $value === false ? $default : unserialize($value);
    }

    /**
     * 取所有值
     * @return array
     */
    public function getAttributes()
    {
        $key    = $this->getSaveKey($this->getSessionId());
        $result = $this->redis->hGetAll($key);
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
        $key     = $this->getSaveKey($this->getSessionId());
        $success = $this->redis->hDel($key, $name);
        return $success ? true : false;
    }

    /**
     * 清除session
     * @return bool
     */
    public function clear()
    {
        $key     = $this->getSaveKey($this->getSessionId());
        $success = $this->redis->del($key);
        return $success ? true : false;
    }

    /**
     * 判断是否存在
     * @param string $name
     * @return bool
     */
    public function has(string $name)
    {
        $key   = $this->getSaveKey($this->getSessionId());
        $exist = $this->redis->hExists($key, $name);
        return $exist ? true : false;
    }

}
