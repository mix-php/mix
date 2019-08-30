<?php

namespace App\Tcp\Helpers;

use Mix\Concurrent\Coroutine\Channel;

/**
 * Class SendHelper
 * @package App\Tcp\Helpers
 * @author liu,jian <coder.keda@gmail.com>
 */
class SendHelper
{

    /**
     * Send error
     * @param Channel $sendChan
     * @param $code
     * @param $message
     * @param null $id
     */
    public static function error(Channel $sendChan, $code, $message, $id = null)
    {
        $data = JsonRpcHelper::error($code, $message, $id);
        $sendChan->push($data);
    }

    /**
     * Send result
     * @param Channel $sendChan
     * @param $result
     * @param null $id
     */
    public static function result(Channel $sendChan, $result, $id = null)
    {
        $data = JsonRpcHelper::result($result, $id);
        $sendChan->push($data);
    }

    /**
     * Send notification
     * @param Channel $sendChan
     * @param $result
     * @param null $id
     */
    public static function notification(Channel $sendChan, $method, $result)
    {
        $data = JsonRpcHelper::notification($method, $result);
        $sendChan->push($data);
    }

}
