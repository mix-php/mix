<?php

namespace Mix\SyncInvoke\Event;

/**
 * Class InvokedEvent
 * Client Event
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
     * 闭包原始代码
     * @var string
     */
    public $code;

    /**
     * 执行异常信息
     * @var string|null
     */
    public $error;

}
