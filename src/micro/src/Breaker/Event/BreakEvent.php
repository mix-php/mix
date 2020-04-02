<?php

namespace Mix\Micro\Breaker\Event;

/**
 * Class BreakEvent
 * @package Mix\Micro\Breaker\Event
 */
class BreakEvent
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
