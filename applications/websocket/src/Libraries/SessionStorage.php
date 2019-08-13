<?php

namespace WebSocket\Libraries;

use Mix\Redis\Coroutine\RedisConnection;

/**
 * Class SessionStorage
 * @package WebSocket\Libraries
 * @author liu,jian <coder.keda@gmail.com>
 */
class SessionStorage
{

    /**
     * @var string
     */
    public $joinRoomId;

    /**
     * @var RedisConnection
     */
    public $redis;

    /**
     * 清除
     */
    public function clear()
    {
        $this->redis->disabled = true; // 标记废除
        $this->redis->disconnect();
    }

}
