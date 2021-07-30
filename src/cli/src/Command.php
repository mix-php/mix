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
     * @var \Closure|RunInterface
     */
    public $run;

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
     * @var bool
     */
    public $singleton = false;

    /**
     * @var \Closure[]
     */
    public $handlers = [];

    /**
     * Command constructor.
     * @param string $name
     * @param string $short
     * @param string $long
     * @param \Closure|RunInterface $run
     */
    public function __construct(string $name, string $short, string $long, $run)
    {
        if (!$run instanceof \Closure && !$run instanceof RunInterface) {
            throw new \RuntimeException('\'$run\' type is invalid');
        }

        $this->name = $name;
        $this->short = $short;
        $this->long = $long;
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
