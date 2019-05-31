<?php

namespace Common\Libraries\Listeners;

use Mix\Database\QueryListenerInterface;

/**
 * Class DatabaseListener
 * @package Common\Libraries\Listeners
 * @author liu,jian <coder.keda@gmail.com>
 */
class DatabaseListener implements QueryListenerInterface
{

    /**
     * 监听
     * @param $query
     */
    public function listen($query)
    {
        // TODO: Implement listen() method.
    }

}
