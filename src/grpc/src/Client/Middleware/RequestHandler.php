<?php

namespace Mix\Grpc\Client\Middleware;

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
     * @param \Swoole\Http2\Request $request
     * @return \Swoole\Http2\Response
     */
    public function handle(\Swoole\Http2\Request $request): \Swoole\Http2\Response 
    {
        $middleware = array_shift($this->middleware);
        if (!$middleware) {
            return call_user_func($this->callback, $request);
        }
        return $middleware->process($request, $this);
    }

}
