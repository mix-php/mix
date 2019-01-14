<?php

namespace Common\Libraries;

use Mix\Pool\DialInterface;

/**
 * Class RedisDial
 * @package Common\Libraries
 */
class RedisPoolDial implements DialInterface
{
    
    /**
     * 拨号
     * @return \Mix\Redis\Coroutine\RedisConnection|mixed
     */
    public function dial()
    {
        // TODO: Implement dial() method.
        return \Mix\Redis\Coroutine\RedisConnection::newInstance();
    }

}
