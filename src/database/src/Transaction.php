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
     * @param LoggerInterface|null $logger
     */
    public function __construct(Driver $driver, ?LoggerInterface $logger)
    {
        parent::__construct($driver, $logger);
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
        $this->__destruct();
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
        $this->__destruct();
    }

}
