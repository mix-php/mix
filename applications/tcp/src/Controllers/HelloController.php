<?php

namespace Tcp\Controllers;

use Mix\Helper\JsonHelper;

/**
 * Class Hello
 * @package Tcp\Controllers
 * @author LIUJIAN <coder.keda@gmail.com>
 */
class Hello
{

    /**
     * Method DEMO
     * @param $params
     * @param $id
     */
    public function actionWorld($params, $id)
    {
        $response = [
            'jsonrpc' => '2.0',
            'result'  => [
                'Hello',
                'World',
            ],
            'id'      => $id,
        ];
        app()->tcp->send(JsonHelper::encode($response) . "\n");
    }

}
