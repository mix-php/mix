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
     * 类型
     */
    const TYPE_VALUE = 0;
    const TYPE_CANCEL = 1;

    /**
     * @var ValueContext
     */
    protected $valueContext;

    /**
     * @var CancelContext
     */
    protected $cancelContext;

    /**
     * Get ValueContext
     * @return object
     */
    protected function context(int $type)
    {
        switch ($type) {
            case static::TYPE_VALUE:
                $property = 'valueContext';
                $class    = ValueContext::class;
                break;
            case static::TYPE_CANCEL:
                $property = 'cancelContext';
                $class    = CancelContext::class;
                break;
        }
        if (!isset($this->$property)) {
            $this->$property = new $class();
        }
        return $this->$property;
    }

    /**
     * With value
     * @param string $key
     * @param $value
     */
    public function withValue(string $key, $value)
    {
        /** @var ValueContext $context */
        $context = $this->context(static::TYPE_VALUE);
        $context->withValue($key, $value);
    }

    /**
     * Get value
     * @param string $key
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function value(string $key)
    {
        /** @var ValueContext $context */
        $context = $this->context(static::TYPE_VALUE);
        return $context->value($key);
    }

    /**
     *
     * @return \Closure
     */
    public function withCancel(): \Closure
    {
        /** @var CancelContext $context */
        $context = $this->context(static::TYPE_CANCEL);
        return $context->cancel();
    }

    /**
     * Done
     * @return Channel
     */
    public function done(): Channel
    {
        /** @var CancelContext $context */
        $context = $this->context(static::TYPE_CANCEL);
        return $context->channel();
    }

}
