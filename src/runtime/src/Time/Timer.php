<?php

namespace Mix\Time;

use Mix\Coroutine\Channel;

/**
 * Class Timer
 * @package Mix\Time
 */
class Timer
{

    /**
     * @var int
     */
    protected $timerId;

    /**
     * @var int 单位：ms
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
        $this->timerId      = \Swoole\Timer::after($duration, function () {
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
     * Reset
     * @param int $duration
     */
    public function reset(int $duration)
    {
        if (!$this->timerId) {
            throw new \RuntimeException('Notify has stopped');
        }
        \Swoole\Timer::clear($this->timerId);
        $this->timerId = \Swoole\Timer::after($duration, function () {
            $this->timerChannel->push(microtime(true));
        });
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
