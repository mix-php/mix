<?php

namespace Http\Controllers;

use Http\Helpers\SendHelper;
use Mix\Http\Message\ServerRequest;
use Mix\Http\Message\Response;

/**
 * Class IndexController
 * @package Http\Controllers
 * @author liu,jian <coder.keda@gmail.com>
 */
class IndexController
{

    /**
     * Index
     * @param ServerRequest $request
     * @param Response $response
     * @return Response
     */
    public function index(ServerRequest $request, Response $response)
    {
        $content = 'Hello, World!';
        return SendHelper::html($response, $content);
    }

}
