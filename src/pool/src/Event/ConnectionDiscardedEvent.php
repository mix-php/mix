<?php

namespace Mix\Pool\Event;

/**
 * Class ConnectionDiscardedEvent
 * @package Mix\Pool\Event
 */
class ConnectionDiscardedEvent
{

    /**
     * @var object
     */
    public $connection;

    /**
     * ConnectionDiscardEvent constructor.
     * @param object $connection
     */
    public function __construct(object $connection)
    {
        $this->connection = $connection;
    }

}
