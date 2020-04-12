<?php

namespace Mix\JsonRpc\Middleware;

use Mix\JsonRpc\Message\Request;
use Mix\JsonRpc\Message\Response;

/**
 * Class RequestHandler
 * @package Mix\JsonRpc\Middleware
 */
class RequestHandler
{

    /**
     * @var MiddlewareInterface[]
     */
    public $middleware;

    /**
     * @var callable
     */
    public $callback;

    /**
     * RequestHandler constructor.
     * @param MiddlewareInterface[] $middleware
     * @param callable $callback
     */
    public function __construct(array $middleware, callable $callback)
    {
        $this->middleware = $middleware;
        $this->callback   = $callback;
    }

    /**
     * Handle
     * @param Request $request
     * @return Response $response
     */
    public function handle(Request $request): Response
    {
        $middleware = array_shift($this->middleware);
        if (!$middleware) {
            return call_user_func($this->callback, $request);
        }
        return $middleware->process($request, $this);
    }

}
