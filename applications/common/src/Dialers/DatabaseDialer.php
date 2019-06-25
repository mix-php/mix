<?php

namespace Common\Dialers;

use Mix\Database\Coroutine\PDOConnection;
use Mix\Pool\DialerInterface;

/**
 * Class DatabaseDialer
 * @package Common\Dialers
 * @author liu,jian <coder.keda@gmail.com>
 */
class DatabaseDialer implements DialerInterface
{

    /**
     * 拨号
     * @return PDOConnection
     */
    public function dial()
    {
        /** @var PDOConnection $conn */
        $conn = app()->get(PDOConnection::class);
        return $conn;
    }

}
