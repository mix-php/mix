<?php

namespace Mix\Time;

/**
 * Class Time
 * @package Mix\Time
 */
class Time
{

    const MILLISECOND = 1;
    const SECOND = 1000 * self::MILLISECOND;
    const MINUTE = 60 * self::SECOND;
    const HOUR = 60 * self::MINUTE;

    /**
     * New ticker
     * @param int $duration 单位：Millisecond
     * @return Ticker
     */
    public static function newTicker(int $duration)
    {
        return new Ticker($duration);
    }

    /**
     * New timer
     * @param int $duration 单位：Millisecond
     * @return Timer
     */
    public static function newTimer(int $duration)
    {
        return new Timer($duration);
    }

    /**
     * Sleep
     * @param int $duration 单位：Millisecond
     */
    public static function sleep(int $duration)
    {
        \Swoole\Coroutine\System::sleep($duration / 1000);
    }

}