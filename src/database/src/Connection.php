<?php

namespace Mix\Database;

use Mix\Pool\ConnectionTrait;

/**
 * Class Connection
 * @package Mix\Database
 * @author liu,jian <coder.keda@gmail.com>
 */
class Connection extends \Mix\Database\Persistent\Connection implements ConnectionInterface
{

    use ConnectionTrait {
        ConnectionTrait::release as __release;
    }

    /**
     * 执行方法
     * 当出现未知异常时，主动丢弃，使用户无法归还到池
     * @param $name
     * @param array $arguments
     * @return mixed
     * @throws \Throwable
     */
    protected function call($name, $arguments = [])
    {
        try {
            return parent::call($name, $arguments);
        } catch (\Throwable $e) {
            // 丢弃连接
            $this->discard();
            // 抛出异常
            throw $e;
        }
    }

    /**
     * 释放连接
     * @return bool
     */
    public function release()
    {
        if ($this->inTransaction()) {
            return false;
        }
        return $this->__release();
    }

    /**
     * 析构
     */
    public function __destruct()
    {
        // 丢弃连接
        $this->discard();
    }

}
