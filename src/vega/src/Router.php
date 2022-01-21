<?php

namespace Mix\Vega;

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use Mix\Http\Message\ServerRequest;
use Mix\Http\Message\Stream\StringStream;
use Mix\Vega\Exception\NotFoundException;

/**
 * Trait Router
 * @package Mix\Vega
 */
trait Router
{

    /**
     * @var Dispatcher
     */
    protected $dispatcher;

    /**
     * @var Route[]
     */
    protected $routes;

    /**
     * @var \Closure[]
     */
    protected $handlers = [];

    /**
     * @param \Closure ...$handlers
     * @return Engine
     */
    public function use(\Closure ...$handlers): Engine
    {
        $this->handlers = array_merge($this->handlers, $handlers);
        return $this;
    }

    /**
     * @param string $path
     * @param callable ...$handlers
     * @return Route
     */
    public function handle(string $path, callable ...$handlers): Route
    {
        $route = new Route($this, $path, array_merge($this->handlers, $handlers));
        $this->routes[] = $route;
        return $route;
    }

    /**
     * @param string $path
     * @param \Closure ...$handlers
     * @return Route
     */
    public function handleFunc(string $path, \Closure ...$handlers): Route
    {
        $route = new Route($this, $path, array_merge($this->handlers, $handlers));
        $this->routes[] = $route;
        return $route;
    }

    /**
     * @param string $path
     * @param callable ...$handlers
     * @return Route
     * @deprecated 废弃，请用 handle 替代
     */
    public function handleCall(string $path, callable ...$handlers): Route
    {
        return $this->handle($path, ...$handlers);
    }

    /**
     * @param string $prefix
     * @return SubRouter
     */
    public function pathPrefix(string $prefix): SubRouter
    {
        return new SubRouter($prefix, $this);
    }

    protected function startDispatcher(): void
    {
        $this->dispatcher = \FastRoute\simpleDispatcher(function (RouteCollector $r) {
            foreach ($this->routes as $route) {
                $route->handler()($r);
            }
        });
    }

    /**
     * @param string $method
     * @param string $uri
     * @param Context $ctx
     * @throws NotFoundException
     */
    protected function dispatch(string $method, string $uri, Context $ctx): void
    {
        $routeInfo = $this->dispatcher->dispatch($method, $uri);
        switch ($routeInfo[0]) {
            case \FastRoute\Dispatcher::NOT_FOUND:
                // ... 404 Not Found
                $handler = function (Context $ctx) {
                    throw new NotFoundException('404 Not Found', 404);
                };
                $this->runHandlers(array_merge($this->handlers, [$handler]), $ctx);
                break;
            case \FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                // $allowedMethods = $routeInfo[1];
                // ... 405 Method Not Allowed
                $handler = function (Context $ctx) {
                    throw new NotFoundException('405 Method Not Allowed', 405);
                };
                $this->runHandlers(array_merge($this->handlers, [$handler]), $ctx);
                break;
            case \FastRoute\Dispatcher::FOUND:
                $handler = $routeInfo[1];
                $vars = $routeInfo[2];
                // with $vars
                if ($ctx->request instanceof ServerRequest) {
                    $ctx->request->withRouteParams($vars);
                } else {
                    $ctx->request->param = $vars;
                }
                // call $handler
                $handler($ctx);
                break;
        }
    }

    /**
     * @param array $handlers
     * @param Context $ctx
     */
    public function runHandlers(array $handlers, Context $ctx): void
    {
        if (empty($handlers)) {
            return;
        }

        $this->unshift404Handler($handlers);
        $this->unshiftAbortHandler($handlers);

        $handler = array_shift($handlers);
        $ctx->withHandlers($handlers);
        $handler($ctx);
    }

    /**
     * @param array $handlers
     */
    protected function unshiftAbortHandler(array &$handlers): void
    {
        $handler = function (Context $ctx) {
            try {
                $ctx->next();
            } catch (Abort $abort) {
                // 主动中断
                // 没有信息的Abort，只中断不执行send()
                $code = $abort->getCode();
                $message = $abort->getMessage();
                if ($code != 0) {
                    $ctx->response->withStatus($code);
                }
                if ($message != '') {
                    $body = new StringStream($message);
                    $ctx->response->withBody($body);
                }
                if ($code || $message) {
                    $ctx->response->send();
                }

                // redirect 中断
                if ($ctx->response->getHeaderLine('Location') !== '') {
                    $ctx->response->send();
                }
            }
        };
        array_unshift($handlers, $handler);
    }

    /**
     * @param array $handlers
     */
    protected function unshift404Handler(array &$handlers): void
    {
        $handler = function (Context $ctx) {
            try {
                $ctx->next();
            } catch (NotFoundException $ex) {
                if (in_array($ex->getCode(), [404, 405]) == 404 && in_array($ex->getMessage(), ['404 Not Found', '405 Method Not Allowed'])) {
                    $ctx->abortWithStatusException($ex->getCode(), $ex);
                }
            }
        };
        array_unshift($handlers, $handler);
    }

}
