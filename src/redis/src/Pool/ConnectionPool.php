<?php

namespace Mix\Redis\Pool;

use Mix\Redis\Driver;
use Mix\ObjectPool\AbstractObjectPool;

/**
 * Class ConnectionPool
 * @package Mix\Redis\Pool
 */
class ConnectionPool extends AbstractObjectPool
{

    /**
     * 借用连接
     * @return Driver
     */
    public function borrow(): object
    {
        return parent::borrow();
    }

}
