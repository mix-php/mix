<?php

namespace Mix\SyncInvoke\Pool;

use Mix\Pool\ConnectionPoolInterface;
use Mix\Pool\AbstractConnectionPool;
use Mix\SyncInvoke\Connection;

/**
 * Class ConnectionPool
 * @package Mix\Redis\Pool
 * @author liu,jian <coder.keda@gmail.com>
 */
class ConnectionPool extends AbstractConnectionPool implements ConnectionPoolInterface
{

    /**
     * 获取连接
     * @return Connection
     */
    public function getConnection()
    {
        return parent::getConnection();
    }

}
