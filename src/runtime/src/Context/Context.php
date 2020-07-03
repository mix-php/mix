<?php

namespace Mix\Context;

use Mix\Coroutine\Channel;

/**
 * Class Context
 * @package Mix\Context
 */
class Context
{

    /**
     * @var ValueContext
     */
    protected $value;

    /**
     * Context constructor.
     */
    public function __construct()
    {
        $this->value = new ValueContext();
    }

    /**
     * With value
     * @param string $key
     * @param $value
     */
    public function withValue(string $key, $value)
    {
        $this->value->withValue($key, $value);
    }

    /**
     * Get value
     * @param string $key
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function value(string $key)
    {
        return $this->value->value($key);
    }

    /**
     * With cancel
     * @return CancelContext
     */
    public function withCancel(): CancelContext
    {
        return new CancelContext($this);
    }

    /**
     * With timeout
     * @param int $duration 单位：Millisecond
     * @return TimeoutContext
     */
    public function withTimeout(int $duration): TimeoutContext
    {
        return new TimeoutContext($this, $duration);
    }

}
