<?php

namespace Mix\Database;

/**
 * Class Transaction
 * @package Mix\Database
 */
class Transaction extends Connection
{

    /**
     * Transaction constructor.
     * @param Driver $driver
     */
    public function __construct(Driver $driver)
    {
        parent::__construct($driver);
        if (!$this->driver->instance()->beginTransaction()) {
            throw new \PDOException('Begin transaction failed');
        }
    }

    /**
     * 提交事务
     * @throws \PDOException
     */
    public function commit()
    {
        if (!$this->driver->instance()->commit()) {
            throw new \PDOException('Commit transaction failed');
        }
    }

    /**
     * 回滚事务
     * @throws \PDOException
     */
    public function rollback()
    {
        if (!$this->driver->instance()->rollBack()) {
            throw new \PDOException('Rollback transaction failed');
        }
    }

}
