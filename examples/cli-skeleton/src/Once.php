<?php

namespace App;

class Once
{

    /**
     * @var \Swoole\Coroutine\Channel
     */
    protected $chan;

    /**
     * @var bool
     */
    protected $executed = false;

    public function __construct()
    {
        if (static::isCoroutine()) {
            $this->chan = new \Swoole\Coroutine\Channel(1);
            $this->chan->push(true);
        }
    }

    /**
     * @param \Closure $func
     * @return void
     */
    public function do(\Closure $func): void
    {
        // 同步模式
        if (!isset($this->chan)) {
            if (!$this->executed) {
                $this->executed = true;
                $func();
            }
            return;
        }

        // 协程模式
        $result = $this->chan->pop();
        if ($result) {
            try {
                $this->executed = true;
                $func();
            } finally {
                $this->chan->close();
            }
        }
    }

    /**
     * @return bool
     */
    protected static function isCoroutine(): bool
    {
        if (!class_exists(\Swoole\Coroutine::class)) {
            return false;
        }
        if (\Swoole\Coroutine::getCid() != -1) {
            return true;
        }
        return false;
    }

}
