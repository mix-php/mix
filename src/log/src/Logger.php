<?php

namespace Mix\Log;

/**
 * Class Logger
 * @package Mix\Log
 */
class Logger extends \Monolog\Logger
{

    /**
     * Set name
     */
    public function withName(string $name): \Monolog\Logger
    {
        $this->name = $name;
        return $this;
    }

}
