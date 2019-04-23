<?php

namespace Common\Libraries\Dialers;

use Mix\Pool\DialerInterface;

/**
 * Class RedisDialer
 * @package Common\Libraries\Dialers
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
