<?php

namespace WebSocket\Helpers;

use Mix\Concurrent\Coroutine\Channel;
use Swoole\WebSocket\Frame;

/**
 * Class SendHelper
 * @package WebSocket\Helpers
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
        $frame         = new Frame();
        $frame->opcode = SWOOLE_WEBSOCKET_OPCODE_TEXT;
        $frame->data   = JsonRpcHelper::error($code, $message, $id);
        $sendChan->push($frame);
    }

    /**
     * Send data
     * @param Channel $sendChan
     * @param $result
     * @param null $id
     */
    public static function data(Channel $sendChan, $result, $id = null)
    {
        $frame         = new Frame();
        $frame->opcode = SWOOLE_WEBSOCKET_OPCODE_TEXT;
        $frame->data   = JsonRpcHelper::data($result, $id);
        $sendChan->push($frame);
    }

}
