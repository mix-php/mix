<?php

namespace Mix\Database\Pool;

use Mix\Database\Driver;
use Mix\ObjectPool\AbstractObjectPool;

/**
 * Class ConnectionPool
 * @package Mix\Database\Pool
 * @author liu,jian <coder.keda@gmail.com>
 */
class ConnectionPool extends AbstractObjectPool
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
