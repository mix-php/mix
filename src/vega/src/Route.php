<?php

namespace Mix\Vega;

use FastRoute\RouteCollector;

/**
 * Class Route
 * @package Mix\Vega
 */
class Route
{

    /**
     * @var Engine
     */
    protected $router;

    /**
     * @var string[]
     */
    protected $methods = [];

    /**
     * @var string
     */
    protected $path;

    /**
     * @var \Closure[]
     */
    protected $handlers;

    /**
     * Route constructor.
     * @param $router
     * @param string $path
     * @param array $handlers
     */
    public function __construct($router, string $path, array $handlers)
    {
        $this->router = $router;
        $this->path = $path;
        $this->handlers = $handlers;
    }

    /**
     * @return \Closure
     */
    public function handler(): \Closure
    {
        return function (RouteCollector $r) {
            $r->addRoute($this->methods, $this->path, function (Context $ctx) {
                $this->router->runHandlers($this->handlers, $ctx);
            });
        };
    }

    /**
     * @param string ...$methods
     * @return $this
     */
    public function methods(string ...$methods): Route
    {
        $this->methods = $methods;
        return $this;
    }

}
