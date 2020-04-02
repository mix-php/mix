<?php

namespace Mix\Micro\Breaker\Event;

/**
 * Class StatusChangeEvent
 * @package Mix\Micro\Breaker\Event
 */
class StatusChangeEvent
{

    /**
     * @var string
     */
    public $name;

    /**
     * 状态: open, close
     * @var string
     */
    public $status;

}
