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
     * @var array
     */
    protected $routes;

    /**
     * @var \Closure[]
     */
    protected $handlers = [];

    /**
     * @param \Closure ...$handlers
     * @return RouterInterface
     */
    public function Use(\Closure ...$handlers): RouterInterface
    {
        $this->handlers = array_merge($this->handlers, $handlers);
    }

    /**
     * @param string $path
     * @param \Closure ...$handlers
     * @return RouterInterface
     */
    public function get(string $path, \Closure ...$handlers): RouterInterface
    {
        $this->addRoute('GET', $path, ...$handlers);
    }

    /**
     * @param string $method
     * @param string $path
     * @param \Closure ...$handlers
     * @return RouterInterface
     */
    public function addRoute(string $method, string $path, \Closure ...$handlers): RouterInterface
    {
        $this->routes[] = function (RouteCollector $r) use ($method, $path, $handlers) {
            $r->addRoute($method, $path, function (Context $ctx) use ($handlers) {
                $this->runHandlers(array_merge($this->handlers, $handlers), $ctx);
            });
        };
    }

    protected function startDispatcher(): void
    {
        $this->dispatcher = \FastRoute\simpleDispatcher(function (RouteCollector $r) {
            foreach ($this->routes as $route) {
                $route($r);
            }
        });
    }

    /**
     * @param string $method
     * @param string $uri
     * @param Context $ctx
     * @throws Exception
     */
    public function dispatch(string $method, string $uri, Context $ctx)
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
     * @param $handlers
     * @param Context $ctx
     */
    protected function runHandlers($handlers, Context $ctx): void
    {
        $this->addAbortHandler($handlers);
        $next = null;
        foreach (array_reverse($handlers) as $handler) {
            
        }
    }

    /**
     * @param array $handlers
     * @return array
     */
    protected function addAbortHandler(array &$handlers)
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
