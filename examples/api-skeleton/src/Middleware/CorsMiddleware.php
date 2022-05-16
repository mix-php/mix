<?php

namespace App\Middleware;

use Mix\Vega\Context;

/**
 * Class Cors
 * @package App\Middleware
 */
class CorsMiddleware
{

    /**
     * @return \Closure
     */
    public static function callback(): \Closure
    {
        return function (Context $ctx) {
            $ctx->setHeader('Access-Control-Allow-Origin', '*');
            $ctx->setHeader('Access-Control-Allow-Headers', 'Origin, Accept, Keep-Alive, User-Agent, Cache-Control, Content-Type, X-Requested-With, Authorization');
            $ctx->setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, HEAD, OPTIONS');
            if ($ctx->request->getMethod() == 'OPTIONS') {
                $ctx->abortWithStatus(200);
            }

            $ctx->next();
        };
    }

}
