<?php

namespace Mix\Context;

use Mix\Coroutine\Channel;

/**
 * Class CancelContext
 * @package Mix\Context
 */
class CancelContext
{

    /**
     * @var Channel
     */
    protected $channel;

    /**
     * CancelContext constructor.
     */
    public function __construct()
    {
        $this->channel = new Channel();
    }

    /**
     * Cancel
     * @return \Closure
     */
    public function cancel(): \Closure
    {
        return function () {
            $this->channel->push(new \stdClass());
        };
    }

    /**
     * Channel
     * @return Channel
     */
    public function channel(): Channel
    {
        return $this->channel;
    }

}
