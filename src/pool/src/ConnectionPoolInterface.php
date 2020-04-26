<?php

namespace Mix\Pool;

/**
 * Interface ConnectionPoolInterface
 * @package Mix\Pool
 * @author liu,jian <coder.keda@gmail.com>
 */
interface ConnectionPoolInterface
{

    /**
     * 获取连接
     * @return object
     */
    public function get();

    /**
     * 释放连接
     * @param object $connection
     * @return bool
     */
    public function release(object $connection);

    /**
     * 丢弃连接
     * @param object $connection
     * @return bool
     */
    public function discard(object $connection);

    /**
     * 获取连接池的统计信息
     * @return array
     */
    public function stats();

}
