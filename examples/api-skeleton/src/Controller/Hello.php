<?php

namespace App\Controller;

use Mix\Vega\Context;

class Hello
{

    /**
     * @param Context $ctx
     */
    public function index(Context $ctx)
    {
        $ctx->string(200, 'hello, world!');
    }

}
