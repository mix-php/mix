<?php

namespace Mix\SyncInvoke\Event;

/**
 * Class InvokedEvent
 * @package Mix\SyncInvoke\Event
 */
class InvokedEvent
{

    /**
     * 执行时间 (ms)
     * @var float
     */
    public $time;

    /**
     * 闭包原始数据
     * @var string
     */
    public $raw;

    /**
     * 执行异常信息
     * @var string
     */
    public $error;

}
