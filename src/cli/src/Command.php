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
     * 外部要用,无法私有
     * @var \Closure[]
     */
    public $handlers = [];

    /**
     * Command constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        foreach ($config as $key => $value) {
            $this->$key = $value;
        }

        if (!$this->run instanceof \Closure && !$this->run instanceof RunInterface) {
            throw new \RuntimeException('\'$run\' type is invalid');
        }
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
     * @return $this
     */
    public function use(\Closure ...$handlerFunc): Command
    {
        array_push($this->handlers, ...$handlerFunc);
        return $this;
    }

}
