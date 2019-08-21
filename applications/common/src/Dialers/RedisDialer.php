<?php

namespace Common\Dialers;

use Mix\Pool\DialerInterface;
use Mix\Redis\Coroutine\RedisConnection;

/**
 * Class RedisDialer
 * @package Common\Dialers
 * @author liu,jian <coder.keda@gmail.com>
 */
class RedisDialer implements DialerInterface
{

    /**
     * 拨号
     * @return RedisConnection
     */
    public function dial()
    {
        // 创建一个连接并返回
        return context()->get(RedisConnection::class);
    }

}
