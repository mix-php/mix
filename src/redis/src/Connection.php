<?php

namespace Mix\Redis;

use Mix\Pool\ConnectionTrait;

/**
 * Class Connection
 * @package Mix\Redis
 * @author liu,jian <coder.keda@gmail.com>
 */
class Connection extends \Mix\Redis\Persistent\Connection implements ConnectionInterface
{

    use ConnectionTrait;

    /**
     * 执行命令
     * 当出现未知异常时，主动丢弃，使用户无法归还到池
     * @param $name
     * @param array $arguments
     * @return mixed
     * @throws \Throwable
     */
    public function __call($name, $arguments = [])
    {
        try {
            return parent::__call($name, $arguments);
        } catch (\Throwable $e) {
            // 丢弃连接
            $this->discard();
            // 抛出异常
            throw $e;
        }
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
