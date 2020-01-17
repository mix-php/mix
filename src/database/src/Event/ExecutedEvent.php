<?php

namespace Mix\Database\Event;

/**
 * Class ExecutedEvent
 * @package Mix\Database\Event
 * @author liu,jian <coder.keda@gmail.com>
 */
class ExecutedEvent
{

    /**
     * @var string
     */
    public $sql = '';

    /**
     * @var array
     */
    public $bindings = [];

    /**
     * @var float
     */
    public $time = 0;

}
