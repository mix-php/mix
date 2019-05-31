<?php

namespace Common\Listeners;

use Mix\Redis\ExecuteListenerInterface;

/**
 * Class RedisListener
 * @package Common\Listeners
 * @author liu,jian <coder.keda@gmail.com>
 */
class RedisListener implements ExecuteListenerInterface
{

    /**
     * 监听
     * @param array $cmd
     */
    public function listen($cmd)
    {
        // TODO: Implement listen() method.
    }

}
