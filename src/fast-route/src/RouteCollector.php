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
     * @param string[] $middleware
     * @deprecated 废弃，请使用 match 替代
     */
    public function route($httpMethod, string $route, $handler, array $middleware = [])
    {
        $this->match((array)$httpMethod, $route, $handler, $middleware);
    }

    /**
     * Adds a route to the collection.
     *
     * The syntax used in the $route string depends on the used route parser.
     *
     * @param string[] $httpMethod
     * @param string $route
     * @param callable $handler
     * @param string[] $middleware
     */
    public function match(array $httpMethod, string $route, $handler, array $middleware = [])
    {
        $route      = $this->currentGroupPrefix . $route;
        $routeDatas = $this->routeParser->parse($route);
        foreach ($httpMethod as $method) {
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
     * @param string[] $middleware
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
     * Adds a Any route to the collection
     *
     * This is simply an alias of $this->addRoute('*', $route, $handler)
     *
     * @param string $route
     * @param callable $handler
     * @param string[] $middleware
     */
    public function any(string $route, $handler, array $middleware = [])
    {
        $this->match(['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'HEAD', 'OPTIONS'], $route, $handler, $middleware);
    }

    /**
     * Adds a GET route to the collection
     *
     * This is simply an alias of $this->addRoute('GET', $route, $handler)
     *
     * @param string $route
     * @param callable $handler
     * @param string[] $middleware
     */
    public function get(string $route, $handler, array $middleware = [])
    {
        $this->match(['GET'], $route, $handler, $middleware);
    }

    /**
     * Adds a POST route to the collection
     *
     * This is simply an alias of $this->addRoute('POST', $route, $handler)
     *
     * @param string $route
     * @param callable $handler
     * @param string[] $middleware
     */
    public function post(string $route, $handler, array $middleware = [])
    {
        $this->match(['POST'], $route, $handler, $middleware);
    }

    /**
     * Adds a PUT route to the collection
     *
     * This is simply an alias of $this->addRoute('PUT', $route, $handler)
     *
     * @param string $route
     * @param callable $handler
     * @param string[] $middleware
     */
    public function put(string $route, $handler, array $middleware = [])
    {
        $this->match(['PUT'], $route, $handler, $middleware);
    }

    /**
     * Adds a DELETE route to the collection
     *
     * This is simply an alias of $this->addRoute('DELETE', $route, $handler)
     *
     * @param string $route
     * @param callable $handler
     * @param string[] $middleware
     */
    public function delete(string $route, $handler, array $middleware = [])
    {
        $this->match(['DELETE'], $route, $handler, $middleware);
    }

    /**
     * Adds a PATCH route to the collection
     *
     * This is simply an alias of $this->addRoute('PATCH', $route, $handler)
     *
     * @param string $route
     * @param callable $handler
     * @param string[] $middleware
     */
    public function patch(string $route, $handler, array $middleware = [])
    {
        $this->match(['PATCH'], $route, $handler, $middleware);
    }

    /**
     * Adds a HEAD route to the collection
     *
     * This is simply an alias of $this->addRoute('HEAD', $route, $handler)
     *
     * @param string $route
     * @param callable $handler
     * @param string[] $middleware
     */
    public function head(string $route, $handler, array $middleware = [])
    {
        $this->match(['HEAD'], $route, $handler, $middleware);
    }

    /**
     * Adds a OPTIONS route to the collection
     *
     * This is simply an alias of $this->addRoute('OPTIONS', $route, $handler)
     *
     * @param string $route
     * @param callable $handler
     * @param string[] $middleware
     */
    public function options(string $route, $handler, array $middleware = [])
    {
        $this->match(['OPTIONS'], $route, $handler, $middleware);
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
