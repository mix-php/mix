<?php

namespace Mix\Pool\Event;

/**
 * Class DiscardedEvent
 * @package Mix\Pool\Event
 */
class DiscardedEvent
{

    /**
     * @var object
     */
    public $object;

    /**
     * DiscardedEvent constructor.
     * @param object $object
     */
    public function __construct(object $object)
    {
        $this->object = $object;
    }

}
