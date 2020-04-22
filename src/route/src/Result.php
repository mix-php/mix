<?php

namespace Mix\Route;

/**
 * Class Result
 * @package Mix\Route
 * @author liu,jian <coder.keda@gmail.com>
 */
class Result
{

    /**
     * @var callable
     */
    public $callback;

    /**
     * @var array
     */
    public $middleware;

    /**
     * @var array
     */
    public $params;

    /**
     * Result constructor.
     * @param array $callback
     * @param array $middleware
     * @param array $params
     */
    public function __construct(array $callback, array $middleware, array $params)
    {
        $this->callback   = $callback;
        $this->middleware = $middleware;
        $this->params     = $params;
    }

    /**
     * 获取Callback
     * @return callable
     */
    public function getCallback(): callable
    {
        if ($this->callback instanceof \Closure) {
            return $this->callback;
        }
        list($class, $method) = $this->callback;
        return [is_object($class) ? $class : new $class(), $method];
    }

    /**
     * 获取中间件名称
     * @return array
     */
    public function getMiddleware(): array
    {
        return $this->middleware;
    }

    /**
     * 获取路由参数
     * @return array
     */
    public function getParams(): array
    {
        return $this->params;
    }

}
