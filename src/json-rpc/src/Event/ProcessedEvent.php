<?php

namespace Mix\JsonRpc\Event;

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
     * @var string
     */
    public $method;

    /**
     * 执行异常信息
     * @var string
     */
    public $error;

}
