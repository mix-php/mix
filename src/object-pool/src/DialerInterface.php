<?php

namespace Mix\ObjectPool;

/**
 * Interface DialerInterface
 * @package Mix\ObjectPool
 */
interface DialerInterface
{

    /**
     * 拨号
     * @return object
     */
    public function dial(): object;

}
