<?php

namespace Mix\ObjectPool;

/**
 * Interface DialerInterface
 * @package Mix\ObjectPool
 * @author liu,jian <coder.keda@gmail.com>
 */
interface DialerInterface
{

    /**
     * 拨号
     * @return object
     */
    public function dial();

}
