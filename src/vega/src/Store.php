<?php

namespace Mix\Vega;

use Mix\Vega\Exception\RuntimeException;

/**
 * Trait Store
 * @package Mix\Vega
 */
trait Store
{

    /**
     * @var array
     */
    protected $keys = [];

    /**
     * @param string $key
     * @param $value
     */
    public function set(string $key, $value): void
    {
        $this->keys[$key] = $value;
    }

    /**
     * @param string $key
     * @return mixed|null
     */
    public function get(string $key)
    {
        return $this->keys[$key] ?? null;
    }

    /**
     * @param string $key
     * @return mixed
     * @throws RuntimeException
     */
    public function mustGet(string $key)
    {
        $value = $this->get($key);
        if (is_null($value)) {
            throw new RuntimeException(sprintf('Key %s not found', $key));
        }
        return $value;
    }

}
