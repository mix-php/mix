<?php

namespace Mix\Pool;

/**
 * Trait ConnectionTrait
 * @package Mix\Pool
 * @author liu,jian <coder.keda@gmail.com>
 */
trait ConnectionTrait
{

    /**
     * @var ConnectionPoolInterface
     */
    public $pool;

    /**
     * 丢弃连接
     * @param object $connection
     * @return bool
     */
    public function __discard(object $connection)
    {
        if (isset($this->pool)) {
            return $this->pool->discard($connection);
        }
        return false;
    }

    /**
     * 释放连接
     * @param object $connection
     * @return bool
     */
    public function __release(object $connection)
    {
        if (isset($this->pool)) {
            return $this->pool->release($connection);
        }
        return false;
    }

}
