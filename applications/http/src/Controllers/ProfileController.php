<?php

namespace Http\Controllers;

use Http\Helpers\SendHelper;
use Mix\Http\Message\Response;
use Mix\Http\Message\ServerRequest;

/**
 * Class ProfileController
 * @package Http\Controllers
 * @author liu,jian <coder.keda@gmail.com>
 */
class ProfileController
{

    /**
     * Index
     * @param ServerRequest $request
     * @param Response $response
     * @return Response
     */
    public function index(ServerRequest $request, Response $response)
    {
        $data = [
            'name'    => '小明',
            'age'     => 18,
            'friends' => ['小红', '小花', '小飞'],
        ];
        return SendHelper::view($response, 'profile.index', $data);
    }

}
