<?php

namespace Mix\Signal;

use Swoole\Coroutine\Channel;

/**
 * Class Notify
 * @package Mix\Signal
 */
class Notify
{

    /**
     * @var int[]
     */
    protected $signals;

    /**
     * @var Channel
     */
    protected $signalChannel;

    /**
     * Signal constructor.
     * @param int ...$signals
     */
    public function __construct(int ...$signals)
    {
        $this->signalChannel = new Channel();
        $this->signals       = $signals;
        foreach ($signals as $signal) {
            \Swoole\Process::signal($signal, function ($signal) {
                $this->signalChannel->push($signal);
            });
        }
    }

    /**
     * Stop
     */
    public function stop()
    {
        if (!isset($this->signals)) {
            return;
        }
        foreach ($this->signals as $signal) {
            \Swoole\Process::signal($signal, null);
        }
        $this->signals = null;
        $this->signalChannel->close();
    }

    /**
     * Reset
     * @param int ...$signals
     * @throws \RuntimeException
     */
    public function reset(int ...$signals)
    {
        if (!$this->signals) {
            throw new \RuntimeException('Notify has stopped');
        }
        $this->signals = $signals;
        foreach ($signals as $signal) {
            \Swoole\Process::signal($signal, function ($signal) {
                $this->signalChannel->push($signal);
            });
        }
    }

    /**
     * Get Channel
     * @return Channel
     */
    public function channel(): Channel
    {
        return $this->signalChannel;
    }

}
