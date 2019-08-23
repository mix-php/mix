<?php

namespace App\Tcp\Controllers;

/**
 * Class HelloController
 * @package App\Tcp\Controllers
 * @author liu,jian <coder.keda@gmail.com>
 */
class HelloController
{

    /**
     * Method demo
     * @param $params
     * @return array
     */
    public function world($params)
    {
        return [
            'Hello, World!',
        ];
    }

}
