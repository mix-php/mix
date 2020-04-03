<?php

namespace Mix\JsonRpc\Event;

use Mix\JsonRpc\Message\Request;

/**
 * Class ProcessedEvent
 * @package Mix\JsonRpc\Event
 */
class ProcessedEvent
{

    /**
     * 执行时间 (ms)
     * @var float
     */
    public $time;
    
    /**
     * 执行的方法
     * @var Request
     */
    public $request;

    /**
     * 执行异常信息
     * @var string
     */
    public $error;

}
