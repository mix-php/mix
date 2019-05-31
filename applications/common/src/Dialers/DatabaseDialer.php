<?php

namespace Common\Dialers;

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
     * @return \Mix\Database\Coroutine\PDOConnection
     */
    public function dial()
    {
        return \Mix\Database\Coroutine\PDOConnection::newInstance();
    }

}
