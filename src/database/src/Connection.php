<?php

namespace Mix\Database;

/**
 * Class Connection
 * @package Mix\Database
 */
class Connection extends AbstractConnection
{

    protected $exceptional = false;

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
            return call_user_func_array(parent::class . "::{$name}", $arguments);
        } catch (\Throwable $ex) {
            if (static::isDisconnectException($ex) && !$this->inTransaction()) {
                // 断开连接异常处理
                $this->reconnect();
                // 重连后允许再次执行
                $this->executed = false;
                // 重新执行方法
                return $this->call($name, $arguments);
            } else {
                // 不可在这里处理丢弃连接，会影响用户 try/catch 事务处理业务逻辑
                // 会导致 commit rollback 时为 EmptyDriver
                $this->exceptional = true;

                // 抛出其他异常
                throw $ex;
            }
        }
    }

    public function __destruct()
    {
        $this->executed = true;

        // 回收
        if (!$this->driver || $this->driver instanceof EmptyDriver) {
            return;
        }
        if ($this->exceptional || $this->inTransaction()) {
            $this->driver->__discard();
            $this->driver = new EmptyDriver();
            return;
        }
        $this->driver->__return();
        $this->driver = new EmptyDriver();
    }

}
