<?php

namespace Common\Libraries\Dial;

use Mix\Pool\DialInterface;

/**
 * Class DatabaseDial
 * @package Common\Libraries\Dial
 * @author liu,jian <coder.keda@gmail.com>
 */
class DatabaseDial implements DialInterface
{

    /**
     * 处理
     * @return \Mix\Database\Coroutine\PDOConnection
     */
    public function handle()
    {
        return \Mix\Database\Coroutine\PDOConnection::newInstance();
    }

}
