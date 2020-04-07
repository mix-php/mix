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
     * @var Request[]
     */
    public $requests;

    /**
     * InterceptDispatcher constructor.
     * @param MiddlewareInterface[] $middleware
     * @param callable $callback
     * @param Request[] $requests
     */
    public function __construct(array $middleware, callable $callback, array $requests)
    {
        foreach ($middleware as $class) {
            $object = $class;
            if (!is_object($class)) {
                $object = new $class();
            }
            if (!($object instanceof MiddlewareInterface)) {
                throw new TypeException("{$class} type is not '" . MiddlewareInterface::class . "'");
            }
            $this->middleware[] = $object;
        }
        $this->callback = $callback;
        $this->requests = $requests;
    }

    /**
     * Dispatch
     * @return Response[] $responses
     */
    public function dispatch()
    {
        return (new RequestHandler($this->middleware, $this->callback))->handle($this->requests);
    }

}
