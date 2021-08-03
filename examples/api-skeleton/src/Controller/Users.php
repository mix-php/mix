<?php

namespace App\Controller;

use App\Container\DB;
use Mix\Vega\Context;

class Users
{

    /**
     * @param Context $ctx
     * @throws \Exception
     */
    public function index(Context $ctx)
    {
        $row = DB::instance()->table('users')->where('id = ?', $ctx->param('id'))->first();
        if (!$row) {
            throw new \Exception('User not found');
        }
        $ctx->JSON(200, [
            'code' => 0,
            'message' => 'ok',
            'data' => $row
        ]);
    }

}
