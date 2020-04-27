<?php

namespace Mix\SyncInvoke\Client\Pool;

use Mix\Pool\AbstractConnectionPool;
use Mix\SyncInvoke\Client\Dialer;

/**
 * Class ConnectionPool
 * @package Mix\SyncInvoke\Client\Pool
 */
class ConnectionPool extends AbstractConnectionPool
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
