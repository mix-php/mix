<?php

namespace Mix\SyncInvoke\Client\Pool;

use Mix\ObjectPool\AbstractObjectPool;
use Mix\SyncInvoke\Client\Dialer;

/**
 * Class ConnectionPool
 * @package Mix\SyncInvoke\Client\Pool
 */
class ConnectionPool extends AbstractObjectPool
{

    /**
     * 借用连接
     * @return Dialer
     */
    public function borrow()
    {
        return parent::borrow();
    }

}
