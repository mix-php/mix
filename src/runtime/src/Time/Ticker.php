<?php

namespace Mix\Time;

use Mix\Coroutine\Channel;

/**
 * Class Ticker
 * @package Mix\Time
 */
class Ticker
{

    /**
     * @var int
     */
    protected $timerId;

    /**
     * @var int 单位：Millisecond
     */
    protected $duration;

    /**
     * @var Channel
     */
    protected $timerChannel;

    /**
     * Timer constructor.
     * @param int $duration 单位：Millisecond
     */
    public function __construct(int $duration)
    {
        $this->duration     = $duration;
        $this->timerChannel = new Channel();
        $this->timerId      = \Swoole\Timer::tick($duration, function () {
            $this->timerChannel->push(microtime(true));
        });
    }

    /**
     * Stop
     */
    public function stop()
    {
        if (!$this->timerId) {
            return;
        }
        \Swoole\Timer::clear($this->timerId);
        $this->timerId = null;
        $this->timerChannel->close();
    }

    /**
     * Get Channel
     * @return Channel
     */
    public function channel(): Channel
    {
        return $this->timerChannel;
    }

}
