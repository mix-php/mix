<?php

namespace Common\Libraries;

use Mix\Pool\DialInterface;

/**
 * Class PDODial
 * @package Common\Libraries
 */
class PDODial implements DialInterface
{

    /**
     * 拨号
     * @return \Mix\Database\Coroutine\PDOConnection|mixed
     */
    public function dial()
    {
        // TODO: Implement dial() method.
        return \Mix\Database\Coroutine\PDOConnection::newInstance();
    }

}
