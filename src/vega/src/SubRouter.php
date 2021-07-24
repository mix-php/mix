<?php

namespace Mix\Vega;

/**
 * Class SubRouter
 * @package Mix\Vega
 */
class SubRouter
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
     * @param callable ...$handlers
     * @return Route
     */
    public function handle(string $path, callable ...$handlers): Route
    {
        return $this->engine->handle($this->prefix . $path, ...$handlers);
    }

    /**
     * @param string $path
     * @param \Closure ...$handlers
     * @return Route
     */
    public function handleFunc(string $path, \Closure ...$handlers): Route
    {
        return $this->engine->handleFunc($this->prefix . $path, ...$handlers);
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

}
