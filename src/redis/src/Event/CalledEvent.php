<?php

namespace Mix\Redis\Event;

/**
 * Class CalledEvent
 * @package Mix\Redis\Event
 * @author liu,jian <coder.keda@gmail.com>
 */
class CalledEvent
{

    /**
     * @var string
     */
    public $command = '';

    /**
     * @var array
     */
    public $arguments = [];

    /**
     * @var float
     */
    public $time = 0;

    /**
     * @var string|null
     */
    public $error;

}
