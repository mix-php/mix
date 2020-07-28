<?php

namespace Mix\ObjectPool;

/**
 * Trait ObjectTrait
 * @package Mix\ObjectPool
 * @author liu,jian <coder.keda@gmail.com>
 */
trait ObjectTrait
{

    /**
     * @var AbstractObjectPool
     */
    public $pool;

    /**
     * @var int
     */
    public $createTime = 0;

    /**
     * 丢弃连接
     * @return bool
     */
    public function __discard()
    {
        if (isset($this->pool)) {
            return $this->pool->discard($this);
        }
        return false;
    }

    /**
     * 归还连接
     * @return bool
     */
    public function __return()
    {
        if (isset($this->pool)) {
            return $this->pool->return($this);
        }
        return false;
    }

}
