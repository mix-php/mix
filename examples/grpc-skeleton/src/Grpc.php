<?php

namespace App;

use App\Service\Say;
use Mix\Grpc\Server;

/**
 * Class Grpc
 * @package App
 */
class Grpc
{

    /**
     * @return Server
     */
    public static function new(): Server
    {
        $server = new Server();
        $server->register(Say::class);
        return $server;
    }

}
