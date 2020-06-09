<?php

namespace Mix\FastRoute;

use FastRoute\RouteParser;
use FastRoute\DataGenerator;

/**
 * Class RouteCollector
 * @package Mix\FastRoute
 */
class RouteCollector
{

    /**
     * @var RouteParser
     */
    protected $routeParser;

    /**
     * @var DataGenerator
     */
    protected $dataGenerator;

    /**
     * @var string
     */
    protected $currentGroupPrefix;

    /**
     * @var array
     */
    protected $currentGroupMiddleware;

    /**
     * RouteCollector constructor.
     *
     * @param RouteParser $routeParser
     * @param DataGenerator $dataGenerator
     */
    public function __construct(RouteParser $routeParser, DataGenerator $dataGenerator)
    {
        $this->routeParser            = $routeParser;
        $this->dataGenerator          = $dataGenerator;
        $this->currentGroupPrefix     = '';
        $this->currentGroupMiddleware = [];
    }

    /**
     * Adds a route to the collection.
     *
     * The syntax used in the $route string depends on the used route parser.
     *
     * @param string|string[] $httpMethod
     * @param string $route
     * @param callable $handler
     * @param array $middleware
     */
    public function route($httpMethod, string $route, $handler, array $middleware = [])
    {
        $route      = $this->currentGroupPrefix . $route;
        $routeDatas = $this->routeParser->parse($route);
        foreach ((array)$httpMethod as $method) {
            foreach ($routeDatas as $routeData) {
                $this->dataGenerator->addRoute($method, $routeData, [$handler, array_merge($this->currentGroupMiddleware, $middleware)]);
            }
        }
    }

    /**
     * Create a route group with a common prefix.
     *
     * All routes created in the passed callback will have the given group prefix prepended.
     *
     * @param string $prefix
     * @param callable $callback
     */
    public function group(string $prefix, callable $callback, array $middleware = [])
    {
        $currentGroupMiddleware       = $this->currentGroupMiddleware;
        $this->currentGroupMiddleware = $middleware;

        $previousGroupPrefix      = $this->currentGroupPrefix;
        $this->currentGroupPrefix = $previousGroupPrefix . $prefix;

        $callback($this);

        $this->currentGroupMiddleware = $currentGroupMiddleware;
        $this->currentGroupPrefix     = $previousGroupPrefix;
    }

    /**
     * Adds a GET route to the collection
     *
     * This is simply an alias of $this->addRoute('GET', $route, $handler)
     *
     * @param string $route
     * @param callable $handler
     * @param array $middleware
     */
    public function get(string $route, $handler, array $middleware = [])
    {
        $this->route('GET', $route, $handler, $middleware);
    }

    /**
     * Adds a POST route to the collection
     *
     * This is simply an alias of $this->addRoute('POST', $route, $handler)
     *
     * @param string $route
     * @param callable $handler
     * @param array $middleware
     */
    public function post(string $route, $handler, array $middleware = [])
    {
        $this->route('POST', $route, $handler, $middleware);
    }

    /**
     * Adds a PUT route to the collection
     *
     * This is simply an alias of $this->addRoute('PUT', $route, $handler)
     *
     * @param string $route
     * @param callable $handler
     * @param array $middleware
     */
    public function put(string $route, $handler, array $middleware = [])
    {
        $this->route('PUT', $route, $handler, $middleware);
    }

    /**
     * Adds a DELETE route to the collection
     *
     * This is simply an alias of $this->addRoute('DELETE', $route, $handler)
     *
     * @param string $route
     * @param callable $handler
     * @param array $middleware
     */
    public function delete(string $route, $handler, array $middleware = [])
    {
        $this->route('DELETE', $route, $handler, $middleware);
    }

    /**
     * Adds a PATCH route to the collection
     *
     * This is simply an alias of $this->addRoute('PATCH', $route, $handler)
     *
     * @param string $route
     * @param callable $handler
     * @param array $middleware
     */
    public function patch(string $route, $handler, array $middleware = [])
    {
        $this->route('PATCH', $route, $handler, $middleware);
    }

    /**
     * Adds a HEAD route to the collection
     *
     * This is simply an alias of $this->addRoute('HEAD', $route, $handler)
     *
     * @param string $route
     * @param callable $handler
     * @param array $middleware
     */
    public function head(string $route, $handler, array $middleware = [])
    {
        $this->route('HEAD', $route, $handler, $middleware);
    }

    /**
     * Returns the collected route data, as provided by the data generator.
     *
     * @return array
     */
    public function getData()
    {
        return $this->dataGenerator->getData();
    }

}
