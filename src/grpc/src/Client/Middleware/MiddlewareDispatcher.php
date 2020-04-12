<?php

namespace Mix\Grpc\Middleware;

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
        $this->requests   = $requests;
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
