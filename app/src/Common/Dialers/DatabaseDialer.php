<?php

namespace App\Common\Dialers;

use Mix\Database\Coroutine\Connection;
use Mix\Pool\DialerInterface;

/**
 * Class DatabaseDialer
 * @package App\Common\Dialers
 * @author liu,jian <coder.keda@gmail.com>
 */
class DatabaseDialer implements DialerInterface
{

    /**
     * 拨号
     * @return Connection
     */
    public function dial()
    {
        // 创建一个连接并返回
        return context()->get(Connection::class);
    }

}
