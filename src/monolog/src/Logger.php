<?php

namespace Mix\Monolog;

/**
 * Class Logger
 * @package Mix\Monolog
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
