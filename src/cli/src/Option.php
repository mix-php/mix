<?php

namespace Mix\Cli;

/**
 * Class Option
 * @package Mix\Cli
 */
class Option
{

    /**
     * @var array
     */
    public $names = [];

    /**
     * @var string
     */
    public $usage = '';

    /**
     * Option constructor.
     * @param array $names
     * @param string $usage
     */
    public function __construct(array $names, string $usage)
    {
        $this->names = $names;
        $this->usage = $usage;
    }

}
