<?php

namespace App\Http\Controllers;

use App\Http\Helpers\SendHelper;
use Mix\Http\Message\Response;
use Mix\Http\Message\ServerRequest;

/**
 * Class ProfileController
 * @package App\Http\Controllers
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
            'id'      => $request->getAttribute('id'),
            'name'    => '小明',
            'age'     => 18,
            'friends' => ['小红', '小花', '小飞'],
        ];
        return SendHelper::view($response, 'profile.index', $data);
    }

}
