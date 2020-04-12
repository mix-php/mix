<?php

namespace Mix\Grpc\Middleware;

use Mix\Grpc\Client\Parameters;
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
     * @param Parameters $parameters
     * @return object
     */
    public function handle(Parameters $parameters): object 
    {
        $middleware = array_shift($this->middleware);
        if (!$middleware) {
            return call_user_func($this->callback, $parameters);
        }
        return $middleware->process($parameters, $this);
    }

}
