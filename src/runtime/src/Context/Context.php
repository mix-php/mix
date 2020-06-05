<?php

namespace Mix\Context;

/**
 * Class Context
 * @package Mix\Context
 */
class Context
{

    /**
     * @var ValueContext
     */
    protected $valueContext;

    /**
     * New
     * @return static
     */
    public static function new()
    {
        return new static();
    }

    /**
     * Get ValueContext
     * @return ValueContext
     */
    protected function getValueContext()
    {
        if (!isset($this->valueContext)) {
            $this->valueContext = new ValueContext();
        }
        return $this->valueContext;
    }

    /**
     * With value
     * @param string $key
     * @param $value
     */
    public function withValue(string $key, $value)
    {
        $this->getValueContext()->withValue($key, $value);
    }

    /**
     * Get value
     * @param string $key
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function value(string $key)
    {
        return $this->getValueContext()->value($key);
    }

}
