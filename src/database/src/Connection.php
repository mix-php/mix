<?php

namespace Mix\Database;

/**
 * Class Connection
 * @package Mix\Database
 */
class Connection extends AbstractConnection
{

    /**
     * 返回结果集
     * @return \PDOStatement
     */
    public function query(): \PDOStatement
    {
        return $this->call(__FUNCTION__);
    }

    /**
     * 返回一行
     * @param int $fetchStyle
     * @return array|object
     */
    public function queryOne(int $fetchStyle = null)
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    /**
     * 返回多行
     * @param int $fetchStyle
     * @return array
     */
    public function queryAll(int $fetchStyle = null): array
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    /**
     * 返回一列 (默认第一列)
     * @param int $columnNumber
     * @return array
     */
    public function queryColumn(int $columnNumber = 0): array
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    /**
     * 返回一个标量值
     * @return mixed
     */
    public function queryScalar()
    {
        return $this->call(__FUNCTION__);
    }

    /**
     * 执行SQL语句
     * @return bool
     */
    public function execute(): bool
    {
        return $this->call(__FUNCTION__);
    }

    /**
     * 开始事务
     * @return Transaction
     * @throws \PDOException
     */
    public function beginTransaction(): Transaction
    {
        return $this->call(__FUNCTION__);
    }

    /**
     * 执行方法
     * @param $name
     * @param array $arguments
     * @return mixed
     * @throws \Throwable
     */
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
                $this->driver->__discard();
                // 抛出其他异常
                throw $ex;
            }
        }
    }

    /**
     * 析构
     */
    public function __destruct()
    {
        if ($this->inTransaction()) {
            $this->driver->__discard();
            return;
        }
        $this->driver->__return();
    }

}
