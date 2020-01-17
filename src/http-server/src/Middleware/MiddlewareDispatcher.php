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
     * @var ServerRequestInterface
     */
    public $request;

    /**
     * @var ResponseInterface
     */
    public $response;

    /**
     * MiddlewareDispatcher constructor.
     * @param array $middleware
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     */
    public function __construct(array $middleware, ServerRequestInterface $request, ResponseInterface $response)
    {
        $this->request  = $request;
        $this->response = $response;
        foreach ($middleware as $class) {
            $object = new $class(
                $request,
                $response
            );
            if (!($object instanceof MiddlewareInterface)) {
                throw new TypeException("{$class} type is not '" . MiddlewareInterface::class . "'");
            }
            $this->middleware[] = $object;
        }
    }

    /**
     * 调度
     * @return ResponseInterface
     */
    public function dispatch(): ResponseInterface
    {
        return (new RequestHandler($this->middleware, $this->response))->handle($this->request);
    }

}
