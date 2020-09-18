<?php

namespace Mix\Sync;

use Swoole\Coroutine\Channel;

/**
 * Class WaitGroup
 * @package Mix\Sync
 */
class WaitGroup
{

    /**
     * @var int
     */
    protected $_count = 0;

    /**
     * @var Channel
     */
    protected $_chan;

    /**
     * 使用静态方法创建实例
     * @return $this
     * @deprecated 废弃，请直接使用 new
     */
    public static function new()
    {
        return new static();
    }

    /**
     * WaitGroup constructor.
     */
    public function __construct()
    {
        $this->_chan = new Channel();
    }

    /**
     * 增加
     * @param int $num
     */
    public function add($num = 1)
    {
        $this->_count += $num;
    }

    /**
     * 完成
     * @return bool
     */
    public function done()
    {
        return $this->_chan->push(true);
    }

    /**
     * 等待
     * @return bool
     */
    public function wait()
    {
        for ($i = 0; $i < $this->_count; $i++) {
            $this->_chan->pop();
        }
        return true;
    }

}
