<?php

namespace Mix\Pool;

/**
 * Interface DialerInterface
 * @package Mix\Pool
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
