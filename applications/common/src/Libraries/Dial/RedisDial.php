<?php

namespace Common\Libraries\Dial;

use Mix\Pool\DialInterface;

/**
 * Class RedisDial
 * @package Common\Libraries\Dial
 * @author liu,jian <coder.keda@gmail.com>
 */
class RedisDial implements DialInterface
{

    /**
     * 处理
     * @return \Mix\Redis\Coroutine\RedisConnection
     */
    public function handle()
    {
        return \Mix\Redis\Coroutine\RedisConnection::newInstance();
    }

}
