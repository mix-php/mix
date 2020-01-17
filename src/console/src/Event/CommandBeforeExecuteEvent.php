<?php

namespace Mix\Console\Event;

/**
 * Class CommandBeforeExecuteEvent
 * @package Mix\Console\Event
 */
class CommandBeforeExecuteEvent
{

    /**
     * @var string
     */
    public $command;

    /**
     * CommandBeforeExecuteEvent constructor.
     * @param string $command
     */
    public function __construct(string $command)
    {
        $this->command = $command;
    }

}
