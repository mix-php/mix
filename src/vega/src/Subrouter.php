<?php

namespace Mix\Vega;

/**
 * Class Subrouter
 * @package Mix\Vega
 */
class Subrouter
{

    /**
     * @var string
     */
    protected $prefix;

    /**
     * @var Engine
     */
    protected $engine;

    /**
     * Subrouter constructor.
     * @param string $prefix
     * @param Engine $engine
     */
    public function __construct(string $prefix, Engine $engine)
    {
        $this->prefix = $prefix;
        $this->engine = $engine;
    }

    /**
     * @param string $path
     * @param \Closure ...$handlers
     * @return Route
     */
    public function handleF(string $path, \Closure ...$handlers): Route
    {
        return $this->engine->handleF($this->prefix . $path, ...$handlers);
    }

    /**
     * @param string $path
     * @param callable ...$handlers
     * @return Route
     */
    public function handleC(string $path, callable ...$handlers): Route
    {
        return $this->engine->handleC($this->prefix . $path, ...$handlers);
    }

}
