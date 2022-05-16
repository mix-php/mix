<?php

namespace App\Middleware;

use Firebase\JWT\JWT;
use Mix\Vega\Context;

/**
 * Class Auth
 * @package App\Middleware
 */
class AuthMiddleware
{

    /**
     * @return \Closure
     */
    public static function callback(): \Closure
    {
        return function (Context $ctx) {
            try {
                list(, $token) = explode(' ', $ctx->header('authorization'));
                $payload = JWT::decode($token, $_ENV['JWT_KEY'], ['HS256']);
            } catch (\Throwable $e) {
                $ctx->abortWithStatus(403);
            }

            // 把 Payload 放入上下文，方便其他位置调用
            $ctx->set('payload', $payload);

            $ctx->next();
        };
    }

}
