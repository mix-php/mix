<?php

namespace Mix\ObjectPool\Event;

/**
 * Class DiscardedEvent
 * @package Mix\ObjectPool\Event
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
