<?php

namespace App\Controller;

use App\Container\Upgrader;
use App\Service\Session;
use Mix\Vega\Context;

class WebSocket
{

    /**
     * @param Context $ctx
     */
    public function index(Context $ctx)
    {
        $conn = Upgrader::instance()->upgrade($ctx->request, $ctx->response);
        $session = new Session($conn);
        $session->start();
    }

}
