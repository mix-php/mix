<?php

namespace Mix\Vega;

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
     * @throws Exception
     */
    public function mustGet(string $key)
    {
        $value = $this->get($key);
        if (is_null($value)) {
            throw new Exception(sprintf('Key %s not found', $key));
        }
        return $value;
    }

}
