<?php

namespace Mix\Database\Persistent;

use Mix\Database\AbstractConnection;

/**
 * Class Connection
 * @package Mix\Database\Persistent
 * @author liu,jian <coder.keda@gmail.com>
 */
class Connection extends AbstractConnection
{

    /**
     * 返回结果集
     * @return \PDOStatement
     */
    public function query()
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
    public function queryAll(int $fetchStyle = null)
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    /**
     * 返回一列 (默认第一列)
     * @param int $columnNumber
     * @return array
     */
    public function queryColumn(int $columnNumber = 0)
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
    public function execute()
    {
        return $this->call(__FUNCTION__);
    }

    /**
     * 开始事务
     * @return bool
     */
    public function beginTransaction()
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
        } catch (\Throwable $e) {
            if (static::isDisconnectException($e) && !$this->inTransaction()) {
                // 断开连接异常处理
                $this->reconnect();
                // 重新执行方法
                return $this->call($name, $arguments);
            } else {
                // 抛出其他异常
                throw $e;
            }
        }
    }

    /**
     * 判断是否为断开连接异常
     * @param \Throwable $e
     * @return bool
     */
    protected static function isDisconnectException(\Throwable $e)
    {
        $disconnectMessages = [
            'server has gone away',
            'no connection to the server',
            'Lost connection',
            'is dead or not enabled',
            'Error while sending',
            'decryption failed or bad record mac',
            'server closed the connection unexpectedly',
            'SSL connection has been closed unexpectedly',
            'Error writing data to the connection',
            'Resource deadlock avoided',
            'failed with errno',
        ];
        $errorMessage       = $e->getMessage();
        foreach ($disconnectMessages as $message) {
            if (false !== stripos($errorMessage, $message)) {
                return true;
            }
        }
        return false;
    }

    /**
     * 重新连接
     */
    protected function reconnect()
    {
        $this->close();
        $this->connect();
    }

}
