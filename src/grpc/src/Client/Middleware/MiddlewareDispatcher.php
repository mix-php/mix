<?php

namespace Mix\Grpc\Client\Middleware;

use Mix\Grpc\Client\Message\Request;

/**
 * Class MiddlewareDispatcher
 * @package Mix\Grpc\Client\Middleware
 */
class MiddlewareDispatcher
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
     * @var Request
     */
    public $request;

    /**
     * InterceptDispatcher constructor.
     * @param MiddlewareInterface[] $middleware
     * @param callable $callback
     * @param \Swoole\Http2\Request $request
     */
    public function __construct(array $middleware, callable $callback, \Swoole\Http2\Request $request)
    {
        $instances = [];
        foreach ($middleware as $class) {
            $object = $class;
            if (!is_object($class)) {
                $object = new $class();
            }
            if (!($object instanceof MiddlewareInterface)) {
                throw new TypeException("{$class} type is not '" . MiddlewareInterface::class . "'");
            }
            $instances[] = $object;
        }
        $this->middleware = $instances;
        $this->callback   = $callback;
        $this->request    = $request;
    }

    /**
     * Dispatch
     * @return \Swoole\Http2\Response
     */
    public function dispatch(): \Swoole\Http2\Response
    {
        return (new RequestHandler($this->middleware, $this->callback))->handle($this->request);
    }

}
