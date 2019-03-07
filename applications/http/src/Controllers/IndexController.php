<?php

namespace Http\Controllers;

use Mix\Http\Message\Request;
use Mix\Http\Message\Response;

/**
 * Class IndexController
 * @package Http\Controllers
 * @author LIUJIAN <coder.keda@gmail.com>
 */
class IndexController
{

    /**
     * 默认动作
     * @param Request $request
     * @param Response $response
     * @return string
     */
    public function actionIndex(Request $request, Response $response)
    {
        return 'Hello, World!';
    }

}
