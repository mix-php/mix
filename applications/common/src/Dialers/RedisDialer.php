<?php

namespace Common\Dialers;

use Mix\Pool\DialerInterface;

/**
 * Class RedisDialer
 * @package Common\Dialers
 * @author liu,jian <coder.keda@gmail.com>
 */
class RedisDialer implements DialerInterface
{

    /**
     * 拨号
     * @return \Mix\Redis\Coroutine\RedisConnection
     */
    public function dial()
    {
        return \Mix\Redis\Coroutine\RedisConnection::newInstance();
    }

}
