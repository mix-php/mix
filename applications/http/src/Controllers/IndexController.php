<?php

namespace Http\Controllers;

use Mix\Http\Message\Request;
use Mix\Http\Message\Response;

/**
 * Class IndexController
 * @package Http\Controllers
 * @author liu,jian <coder.keda@gmail.com>
 */
class IndexController
{

    /**
     * 默认动作
     * @return string
     */
    public function actionIndex(Request $request, Response $response)
    {
        return 'Hello, World!';
    }

}
