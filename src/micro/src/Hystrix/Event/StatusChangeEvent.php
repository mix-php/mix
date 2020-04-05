<?php

namespace Mix\Micro\Hystrix\Event;

/**
 * Class StatusChangeEvent
 * @package Mix\Micro\Hystrix\Event
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
