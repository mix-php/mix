<?php

namespace Mix\Vega;

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;

/**
 * Trait RouterTrait
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
     * @return
     */
    public function use(\Closure ...$handlers): Router
    {
        $this->handlers = array_merge($this->handlers, $handlers);
    }

    /**
     * @param string $path
     * @param \Closure ...$handlers
     * @return Route
     */
    public function handleF(string $path, \Closure ...$handlers): Route
    {
        $route = new Route($this, $path, array_merge($this->handlers, $handlers));
        $this->routes[] = $route;
        return $route;
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
     * @throws Exception
     */
    protected function dispatch(string $method, string $uri, Context $ctx)
    {
        $routeInfo = $this->dispatcher->dispatch($method, $uri);
        switch ($routeInfo[0]) {
            case \FastRoute\Dispatcher::NOT_FOUND:
                // ... 404 Not Found
                throw new Exception('404 Not Found', 404);
            case \FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                $allowedMethods = $routeInfo[1];
                // ... 405 Method Not Allowed
                throw new Exception('405 Method Not Allowed', 405);
            case \FastRoute\Dispatcher::FOUND:
                $handler = $routeInfo[1];
                $vars = $routeInfo[2];
                // with $vars
                foreach ($vars as $key => $val) {
                    $ctx->request->withAttribute($key, $val);
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

        $this->addAbortHandler($handlers);

        $arr = array_reverse($handlers);
        $handler = array_pop($arr);
        $ctx->withHandlers($arr);
        $handler($ctx);
    }

    /**
     * @param array $handlers
     */
    protected function addAbortHandler(array &$handlers): void
    {
        $handler = function (Context $ctx) {
            try {
                $ctx->next();
            } catch (Abort $abort) {
            }
        };
        array_push($handlers, $handler);
    }

}
