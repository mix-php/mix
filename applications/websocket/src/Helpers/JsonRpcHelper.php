<?php

namespace WebSocket\Helpers;

use Mix\Helper\JsonHelper;

/**
 * Class JsonRpcHelper
 * @package WebSocket\Helpers
 * @author liu,jian <coder.keda@gmail.com>
 */
class JsonRpcHelper
{

    /**
     * Error
     * @param $code
     * @param $message
     * @param null $id
     * @return string
     */
    public static function error($code, $message, $id = null)
    {
        $data = [
            'jsonrpc' => '2.0',
            'error'   => [
                'code'    => $code,
                'message' => $message,
            ],
            'id'      => $id,
        ];
        return JsonHelper::encode($data, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Data
     * @param $result
     * @param null $id
     * @return string
     */
    public static function data($result, $id = null)
    {
        $data = [
            'jsonrpc' => '2.0',
            'error'   => null,
            'result'  => $result,
            'id'      => $id,
        ];
        return JsonHelper::encode($data, JSON_UNESCAPED_UNICODE);
    }

}
