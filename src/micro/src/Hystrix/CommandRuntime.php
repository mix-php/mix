<?php

namespace Mix\Micro\Hystrix;

/**
 * Class CommandRuntime
 * @package Mix\Micro\Hystrix
 */
class CommandRuntime
{

    /**
     * @var string[]
     */
    public $currentRequests = [];

    /**
     * @var array
     */
    public $sampling = [];

    /**
     * 状态值
     */
    const STATUS_OPEN = 'open';
    const STATUS_CLOSE = 'close';

    /**
     * @var int
     */
    public $status = self::STATUS_CLOSE;

    /**
     * @var int
     */
    public $opentime = 0;

    /**
     * Set status
     * @param int $status
     */
    public function status(string $status)
    {
        if ($status == static::STATUS_OPEN) {
            $this->opentime = static::microtime();
            $this->status   = static::STATUS_OPEN;
            return;
        }
        $this->opentime = 0;
        $this->status   = static::STATUS_CLOSE;
    }

    /**
     * 获取当前时间, 单位: 秒, 粒度: 微秒
     * @return float
     */
    public static function microtime()
    {
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }

}
