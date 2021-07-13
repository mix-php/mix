<?php

namespace Mix\Grpc;

use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Coroutine\Http2\Client;

class Context
{

    /**
     * @var Request
     */
    public $request;

    /**
     * @var Response
     */
    public $response;

    /**
     * @var Client
     */
    public $client;

    /**
     * @param Request $request
     * @param Response $response
     * @return Context
     */
    public static function fromSwoole(Request $request, Response $response): Context
    {
        $ctx = new self();
        $ctx->request = $request;
        $ctx->response = $response;
        return $ctx;
    }

    /**
     * @param Client $client
     * @return Context
     */
    public static function fromClient(Client $client): Context
    {
        $ctx = new self();
        $ctx->client = $client;
        return $ctx;
    }

}
