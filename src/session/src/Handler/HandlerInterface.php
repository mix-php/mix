<?php

namespace Mix\Session\Handler;

/**
 * Interface SessionHandlerInterface
 * @package Mix\Session\Handler
 * @author liu,jian <coder.keda@gmail.com>
 */
interface HandlerInterface
{

    /**
     * 判断 session_id 是否存在
     * @param string $sessionId
     * @return bool
     */
    public function exists(string $sessionId);

    /**
     * 更新生存时间
     * @param string $sessionId
     * @param int $maxLifetime
     * @return bool
     */
    public function expire(string $sessionId, int $maxLifetime);

    /**
     * 赋值
     * @param string $sessionId
     * @param string $name
     * @param $value
     * @return bool
     */
    public function set(string $sessionId, string $name, $value);

    /**
     * 取值
     * @param string $sessionId
     * @param string $name
     * @param null $default
     * @return mixed
     */
    public function get(string $sessionId, string $name, $default = null);

    /**
     * 取所有值
     * @param string $sessionId
     * @return array
     */
    public function all(string $sessionId);

    /**
     * 删除
     * @param string $sessionId
     * @param string $name
     * @return bool
     */
    public function delete(string $sessionId, string $name);

    /**
     * 清除session
     * @param string $sessionId
     * @return bool
     */
    public function clear(string $sessionId);

    /**
     * 判断是否存在
     * @param string $sessionId
     * @param string $name
     * @return bool
     */
    public function has(string $sessionId, string $name);

}
