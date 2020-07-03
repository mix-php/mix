<?php

namespace Mix\Context;

use Mix\Coroutine\Channel;

/**
 * Class CancelContext
 * @package Mix\Context
 */
class CancelContext extends Context
{

    /**
     * @var Context
     */
    protected $parent;

    /**
     * @var Channel
     */
    protected $channel;

    /**
     * CancelContext constructor.
     * @param Context $parent
     */
    public function __construct(Context $parent)
    {
        $this->parent  = $parent;
        $this->channel = new Channel();
    }

    /**
     * With value
     * @param string $key
     * @param $value
     */
    public function withValue(string $key, $value)
    {
        $this->parent->withValue($key, $value);
    }

    /**
     * Get value
     * @param string $key
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function value(string $key)
    {
        return $this->parent->value($key);
    }

    /**
     * Cancel
     */
    public function cancel()
    {
        $this->channel->push(new \stdClass());
    }

    /**
     * Done
     * @return Channel
     */
    public function done(): Channel
    {
        return $this->channel;
    }

}
