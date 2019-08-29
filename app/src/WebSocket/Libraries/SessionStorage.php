<?php

namespace App\WebSocket\Libraries;

use Mix\Concurrent\Coroutine\Channel;

/**
 * Class SessionStorage
 * @package App\WebSocket\Libraries
 * @author liu,jian <coder.keda@gmail.com>
 */
class SessionStorage
{

    /**
     * @var string
     */
    public $joinRoomId;

    /**
     * @var Channel
     */
    public $subChan;

    /**
     * @var Channel
     */
    public $subStopChan;

    /**
     * 清除
     */
    public function clear()
    {
        $this->subChan and $this->subChan->close();
        $this->subStopChan and $this->subStopChan->close();
    }

}
