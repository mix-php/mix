<?php

namespace Mix\Http\Server\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Server\MiddlewareInterface;

/**
 * Class RequestHandler
 * @package Mix\Http\Server\Middleware
 * @author liu,jian <coder.keda@gmail.com>
 */
class RequestHandler implements RequestHandlerInterface
{

    /**
     * @var MiddlewareInterface[]
     */
    public $middleware;

    /**
     * @var ResponseInterface
     */
    public $response;

    /**
     * RequestHandler constructor.
     * @param array $middleware
     * @param ResponseInterface $response
     */
    public function __construct(array $middleware, ResponseInterface $response)
    {
        $this->middleware = $middleware;
        $this->response   = $response;
    }

    /**
     * Handles a request and produces a response.
     *
     * May call other collaborating code to generate the response.
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $middleware = array_shift($this->middleware);
        if (!$middleware) {
            return $this->response;
        }
        return $middleware->process($request, $this);
    }

}
