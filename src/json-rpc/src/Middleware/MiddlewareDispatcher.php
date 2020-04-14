<?php

namespace Mix\JsonRpc\Middleware;

use Mix\JsonRpc\Middleware\MiddlewareInterface;
use Mix\JsonRpc\Message\Request;
use Mix\JsonRpc\Message\Response;

/**
 * Class MiddlewareDispatcher
 * @package Mix\JsonRpc\Middleware
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
     * @param Request $request
     */
    public function __construct(array $middleware, callable $callback, Request $request)
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
     * @return Response
     */
    public function dispatch()
    {
        return (new RequestHandler($this->middleware, $this->callback))->handle($this->request);
    }

}
