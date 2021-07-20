<?php

namespace Mix\Database;

/**
 * Class Connection
 * @package Mix\Database
 */
class Connection extends AbstractConnection
{

    public function queryOne(int $fetchStyle = null)
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function queryAll(int $fetchStyle = null): array
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function queryColumn(int $columnNumber = 0): array
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function queryScalar()
    {
        return $this->call(__FUNCTION__);
    }

    public function execute(): ConnectionInterface
    {
        return $this->call(__FUNCTION__);
    }

    public function beginTransaction(): Transaction
    {
        return $this->call(__FUNCTION__);
    }

    public function rowCount(): int
    {
        return $this->call(__FUNCTION__);
    }

    protected function call($name, $arguments = [])
    {
        try {
            // 执行父类方法
            return call_user_func_array("parent::{$name}", $arguments);
        } catch (\Throwable $ex) {
            if (static::isDisconnectException($ex) && !$this->inTransaction()) {
                // 断开连接异常处理
                $this->reconnect();
                // 重新执行方法
                return $this->call($name, $arguments);
            } else {
                // 丢弃连接
                if ($this->driver) {
                    $this->driver->__discard();
                    $this->driver = new EmptyDriver();
                }
                // 抛出其他异常
                throw $ex;
            }
        }
    }

    public function __destruct()
    {
        if (!$this->driver || $this->driver instanceof EmptyDriver) {
            return;
        }

        // 回收
        if ($this->inTransaction()) {
            $this->driver->__discard();
            $this->driver = new EmptyDriver();
            return;
        }
        $this->driver->__return();
        $this->driver = new EmptyDriver();
    }

}
