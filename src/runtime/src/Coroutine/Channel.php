<?php

namespace Mix\Coroutine;

/**
 * Class Channel
 * @package Mix\Coroutine
 */
class Channel extends \Swoole\Coroutine\Channel
{

    /**
     * @var \Swoole\Coroutine\Channel[]
     */
    protected $notifies = [];

    /**
     * @var bool
     */
    protected $closed = false;

    /**
     * Push
     * @param $data
     * @param null $timeout
     * @return mixed
     */
    public function push($data, $timeout = null)
    {
        $isFull = $this->isFull();
        if ($isFull) { // 执行过程中变满
            foreach ($this->notifies as $channel) {
                $channel->push(true);
            }
        }
        $result = parent::push($data, $timeout);
        if (!$isFull) {
            foreach ($this->notifies as $channel) {
                $channel->push(true);
            }
        }
        return $result;
    }

    /**
     * Pop
     * @param null $timeout
     * @return mixed
     */
    public function pop($timeout = null)
    {
        $result = parent::pop($timeout);
        foreach ($this->notifies as $channel) {
            $channel->push(true);
        }
        return $result;
    }

    /**
     * Close
     * @return bool
     */
    public function close()
    {
        $this->closed = true;
        $result       = parent::close();
        foreach ($this->notifies as $channel) {
            $channel->close();
        }
        return $result;
    }

    /**
     * @return bool
     */
    public function isClosed()
    {
        return $this->closed;
    }

    /**
     * Add Notifier
     * @param \Swoole\Coroutine\Channel $channel
     */
    public function addNotifier(\Swoole\Coroutine\Channel $channel)
    {
        $id                  = spl_object_id($channel);
        $this->notifies[$id] = $channel;
    }

    /**
     * Del Notifier
     * @param \Swoole\Coroutine\Channel $channel
     */
    public function delNotifier(\Swoole\Coroutine\Channel $channel)
    {
        $id = spl_object_id($channel);
        unset($this->notifies[$id]);
    }

}
