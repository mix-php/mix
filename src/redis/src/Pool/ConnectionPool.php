<?php

namespace Mix\Redis\Pool;

use Mix\Redis\Connection;
use Mix\Pool\AbstractConnectionPool;

/**
 * Class ConnectionPool
 * @package Mix\Redis\Pool
 * @author liu,jian <coder.keda@gmail.com>
 */
class ConnectionPool extends AbstractConnectionPool
{

    /**
     * 获取连接
     * @return Connection
     */
    public function get()
    {
        return parent::get();
    }

}
