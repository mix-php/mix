<?php

namespace Mix\Redis\Pool;

use Mix\Redis\Driver;
use Mix\Pool\AbstractConnectionPool;

/**
 * Class ConnectionPool
 * @package Mix\Redis\Pool
 * @author liu,jian <coder.keda@gmail.com>
 */
class ConnectionPool extends AbstractConnectionPool
{

    /**
     * 借用连接
     * @return Driver
     */
    public function borrow()
    {
        return parent::borrow();
    }

}
