<?php

namespace Mix\Context;

/**
 * Class ValueContext
 * @package Mix\Context
 */
class ValueContext
{

    /**
     * @var \ArrayObject
     */
    protected $storage;

    /**
     * Context constructor.
     */
    public function __construct()
    {
        $this->storage = new \ArrayObject();
    }

    /**
     * With value
     * @param string $key
     * @param $value
     */
    public function withValue(string $key, $value)
    {
        $this->storage[$key] = $value;
    }

    /**
     * Get Value
     * @param string $key
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function value(string $key)
    {
        if (!isset($this->storage[$key])) {
            throw new \InvalidArgumentException(sprintf('Invalid key: %s', $key));
        }
        return $this->storage[$key];
    }

}
