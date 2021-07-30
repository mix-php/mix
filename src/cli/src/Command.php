<?php

namespace Mix\Cli;

/**
 * Class Command
 * @package Mix\Cli
 */
class Command
{

    /**
     * @var string
     */
    public $name = '';

    /**
     * @var string
     */
    public $short = '';

    /**
     * @var string
     */
    public $long = '';

    /**
     * 子命令：'Usage: %s %s [ARG...]'
     * 单命令：'Usage: %s [ARG...]'
     * @var string
     */
    public $usageFormat = '';

    /**
     * @var Option[]
     */
    public $options = [];

    /**
     * @var \Closure|RunInterface
     */
    public $run;

    /**
     * @var bool
     */
    public $singleton = false;

    /**
     * @var \Closure[]
     */
    protected $handlers = [];

    /**
     * Command constructor.
     * @param string $name
     * @param string $short
     * @param \Closure|RunInterface $run
     */
    public function __construct(string $name, string $short, $run)
    {
        $this->name = $name;
        $this->short = $short;
        $this->run = $run;
    }

    /**
     * @param Option ...$options
     * @return $this
     */
    public function addOption(Option ...$options): Command
    {
        array_push($this->options, ...$options);
        return $this;
    }

    /**
     * @param \Closure ...$handlerFunc
     */
    public function use(\Closure ...$handlerFunc)
    {
        array_push($this->handlers, ...$handlerFunc);
    }

}
