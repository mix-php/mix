<?php

namespace Tcp\Helpers;

use Mix\Helper\JsonHelper;
use Mix\Server\Connection;
use Tcp\Commands\StartCommand;

/**
 * Class SendHelper
 * @package Tcp\Helpers
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
        $response = [
            'jsonrpc' => '2.0',
            'error'   => [
                'code'    => $code,
                'message' => $message,
            ],
            'id'      => $id,
        ];
        $conn->send(JsonHelper::encode($response) . StartCommand::EOF);
    }

    /**
     * Send data
     * @param Connection $conn
     * @param $result
     * @param null $id
     */
    public static function data(Connection $conn, $result, $id = null)
    {
        $response = [
            'jsonrpc' => '2.0',
            'error'   => null,
            'result'  => $result,
            'id'      => $id,
        ];
        $conn->send(JsonHelper::encode($response) . StartCommand::EOF);
    }

}
