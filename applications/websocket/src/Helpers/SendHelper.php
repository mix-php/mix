<?php

namespace WebSocket\Helpers;

use Mix\Helper\JsonHelper;
use Mix\WebSocket\Connection;
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
     * @param Connection $conn
     * @param $code
     * @param $message
     * @param null $id
     */
    public static function error(Connection $conn, $code, $message, $id = null)
    {
        $data          = [
            'jsonrpc' => '2.0',
            'error'   => [
                'code'    => $code,
                'message' => $message,
            ],
            'id'      => $id,
        ];
        $frame         = new Frame();
        $frame->opcode = SWOOLE_WEBSOCKET_OPCODE_TEXT;
        $frame->data   = JsonHelper::encode($data);
        $conn->send($frame);
    }

    /**
     * Send data
     * @param Connection $conn
     * @param $result
     * @param null $id
     */
    public static function data(Connection $conn, $result, $id = null)
    {
        $data          = [
            'jsonrpc' => '2.0',
            'error'   => null,
            'result'  => $result,
            'id'      => $id,
        ];
        $frame         = new Frame();
        $frame->opcode = SWOOLE_WEBSOCKET_OPCODE_TEXT;
        $frame->data   = JsonHelper::encode($data);
        $conn->send($frame);
    }

}
