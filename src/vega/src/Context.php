<?php

namespace Mix\Vega;

use Mix\Http\Message\Factory\ResponseFactory;
use Mix\Http\Message\Factory\ServerRequestFactory;
use Mix\Http\Message\Response;
use Mix\Http\Message\ServerRequest;

/**
 * Class Context
 * @package Mix\Vega
 */
class Context
{

    /**
     * @var ServerRequest
     */
    public $request;

    /**
     * @var Response
     */
    public $response;

    /**
     * @param \Swoole\Http\Request $request
     * @param \Swoole\Http\Response $response
     * @return Context
     */
    public static function bySwoole(\Swoole\Http\Request $request, \Swoole\Http\Response $response): Context
    {
        $ctx = new static();
        $requestFactory = new ServerRequestFactory();
        $responseFactory = new ResponseFactory();
        $ctx->request = $requestFactory->createServerRequestFromSwoole($request);
        $ctx->response = $responseFactory->createResponseFromSwoole($response);
        return $ctx;
    }

    public function next(): void
    {

    }

    /**
     * @throws Abort
     */
    public function abort(): void
    {
        throw new Abort();
    }

}
