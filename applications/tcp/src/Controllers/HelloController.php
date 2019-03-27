<?php

namespace Tcp\Controllers;

use Mix\Helper\JsonHelper;

/**
 * Class HelloController
 * @package Tcp\Controllers
 * @author liu,jian <coder.keda@gmail.com>
 */
class HelloController
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
            'error'   => null,
            'result'  => [
                'Hello, World!',
            ],
            'id'      => $id,
        ];
        app()->tcp->send(JsonHelper::encode($response) . "\n");
    }

}
