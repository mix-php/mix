<?php

namespace Mix\Micro\Breaker;

use Mix\Micro\Breaker\Exception\NotFoundException;

/**
 * Class CircuitBreaker
 * @package Mix\Micro\Breaker
 */
class CircuitBreaker
{

    /**
     * @var
     */
    public $config = [];

    /**
     * @var CommandDefinition[]
     */
    protected $definitions = [];

    /**
     * Fuser constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
        $this->parse();
    }

    /**
     * Parse
     */
    protected function parse()
    {
        $definitions = [];
        foreach ($this->config as $item) {
            $definition         = new Command($item);
            $name               = $definition->getName();
            $definitions[$name] = $definition;
        }
        $this->definitions = $definitions;
    }

    /**
     * 获取Command
     * @param string $name
     * @return Command
     * @throws NotFoundException
     */
    public function command(string $name): Command
    {
        if (!isset($this->definitions[$name])) {
            throw new NotFoundException("Command definition '{$name}' not found");
        }
        return $this->definitions[$name];
    }

    /**
     * Do
     * @param string $name
     * @param \Closure $request
     * @param \Closure $fallback
     * @return mixed
     * @throws NotFoundException
     */
    public function do(string $name, \Closure $request, \Closure $fallback)
    {
        $command = $this->command($name);
        $runtime = $command->getRuntime();
        // fuse
        if ($runtime->status == CommandRuntime::STATUS_OPEN) {
            if (CommandRuntime::microtime() - $runtime->opentime >= $command->getSleepWindow()) {
                $runtime->status(CommandRuntime::STATUS_CLOSE);
            }
            return call_user_func($fallback);
        }
        $requestVolume = count($runtime->sampling, 1);
        if ($requestVolume >= $command->getRequestVolumeThreshold()) {
            $errorPercent      = count($runtime->sampling["error"] ?? []) / $requestVolume;
            $runtime->sampling = [];
            if ($errorPercent >= $command->getErrorPercentThreshold() / 100) {
                $runtime->status(CommandRuntime::STATUS_OPEN);
                return call_user_func($fallback);
            } else {
                $runtime->status(CommandRuntime::STATUS_CLOSE);
            }
        }
        // concurrent
        if ($command->getMaxConcurrentRequests() && count($runtime->currentRequests) + 1 >= $command->getMaxConcurrentRequests()) {
            return call_user_func($fallback);
        }
        $id                            = spl_object_hash($request);
        $runtime->currentRequests[$id] = '';
        // call
        try {
            $result                         = call_user_func($request);
            $runtime->sampling["success"][] = $id;
        } catch (\Throwable $ex) {
            $runtime->sampling["error"][] = $id;
            throw $ex;
        } finally {
            unset($runtime->currentRequests[$id]);
        }
        return $result;
    }

}
