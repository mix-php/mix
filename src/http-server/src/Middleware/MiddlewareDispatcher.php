<?php

namespace Mix\Http\Server\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Mix\Http\Server\Exception\TypeException;

/**
 * Class MiddlewareDispatcher
 * @package Mix\Http\Server\Middleware
 * @author liu,jian <coder.keda@gmail.com>
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
     * @var ServerRequestInterface
     */
    public $request;

    /**
     * @var ResponseInterface
     */
    public $response;

    /**
     * MiddlewareDispatcher constructor.
     * @param MiddlewareInterface[] $middleware
     * @param callable $callback
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     */
    public function __construct(array $middleware, callable $callback, ServerRequestInterface $request, ResponseInterface $response)
    {
        $instances = [];
        foreach ($middleware as $class) {
            $object = $class;
            if (!is_object($class)) {
                $object = new $class(
                    $request,
                    $response
                );
            }
            if (!($object instanceof MiddlewareInterface)) {
                throw new TypeException("{$class} type is not '" . MiddlewareInterface::class . "'");
            }
            $instances[] = $object;
        }
        $this->middleware = $instances;
        $this->callback   = $callback;
        $this->request    = $request;
        $this->response   = $response;
    }

    /**
     * 调度
     * @return ResponseInterface
     */
    public function dispatch(): ResponseInterface
    {
        return (new RequestHandler($this->middleware, $this->callback, $this->response))->handle($this->request);
    }

}
