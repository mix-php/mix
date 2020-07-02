<?php

namespace Mix\Context;

use Mix\Coroutine\Channel;
use Mix\Time\Time;

/**
 * Class TimeoutContext
 * @package Mix\Context
 */
class TimeoutContext
{

    /**
     * @var Channel
     */
    protected $channel;

    /**
     * @var int 单位：Millisecond
     */
    protected $duration;

    /**
     * @var \Mix\Time\Timer
     */
    protected $timer;

    /**
     * TimeoutContext constructor.
     * @param int $duration 单位：Millisecond
     */
    public function __construct(int $duration)
    {
        $this->channel  = new Channel();
        $this->duration = $duration;
        $this->timer    = Time::newTimer($duration);
        xgo(function () {
            $this->timer->channel();
            $this->channel->push(new \stdClass());
        });
    }

    /**
     * Cancel
     * @return \Closure
     */
    public function cancel(): \Closure
    {
        return function () {
            $this->timer->stop();
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
