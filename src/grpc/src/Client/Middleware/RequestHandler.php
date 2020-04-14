<?php

namespace Mix\Grpc\Client\Middleware;

use Mix\Grpc\Client\Message\Request;

/**
 * Class RequestHandler
 * @package Mix\Grpc\Client\Middleware
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
     * @return object
     */
    public function handle(Request $request): object 
    {
        $middleware = array_shift($this->middleware);
        if (!$middleware) {
            return call_user_func($this->callback, $request);
        }
        return $middleware->process($request, $this);
    }

}
