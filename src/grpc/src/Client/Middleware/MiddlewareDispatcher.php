<?php

namespace Mix\Grpc\Client\Middleware;

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
     * @var Parameters
     */
    public $parameters;

    /**
     * InterceptDispatcher constructor.
     * @param MiddlewareInterface[] $middleware
     * @param callable $callback
     * @param Request[] $requests
     */
    public function __construct(array $middleware, callable $callback, Parameters $parameters)
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
        $this->parameters = $parameters;
    }

    /**
     * Dispatch
     * @return object
     */
    public function dispatch()
    {
        return (new RequestHandler($this->middleware, $this->callback))->handle($this->parameters);
    }

}
