<?php

namespace Common\Listeners;

use Mix\Database\ExecuteListenerInterface;

/**
 * Class DatabaseListener
 * @package Common\Listeners
 * @author liu,jian <coder.keda@gmail.com>
 */
class DatabaseListener implements ExecuteListenerInterface
{

    /**
     * 监听
     * @param array $query
     */
    public function listen($query)
    {
        // TODO: Implement listen() method.
    }

}
