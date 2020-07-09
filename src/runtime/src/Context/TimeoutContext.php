<?php

namespace Mix\Context;

use Mix\Coroutine\Channel;
use Mix\Time\Time;
use Mix\Coroutine\Coroutine;

/**
 * Class TimeoutContext
 * @package Mix\Context
 */
class TimeoutContext extends CancelContext
{

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
     * @param Context $parent
     * @param int $duration 单位：Millisecond
     */
    public function __construct(Context $parent, int $duration)
    {
        parent::__construct($parent);

        $this->duration = $duration;
        $this->timer    = Time::newTimer($duration);
        Coroutine::create(function () {
            if (!$this->timer->channel()->pop()) {
                return;
            }
            $this->cancel();
        });
    }

    /**
     * Cancel
     */
    public function cancel()
    {
        $this->timer->stop();
        parent::cancel();
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
