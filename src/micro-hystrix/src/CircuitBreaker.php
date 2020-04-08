<?php

namespace Mix\Micro\Hystrix;

use Mix\Micro\Hystrix\Event\StatusChangeEvent;
use Mix\Micro\Hystrix\Exception\NotFoundException;
use Mix\Micro\Hystrix\Exception\TimeoutException;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * Class CircuitBreaker
 * @package Mix\Micro\Hystrix
 */
class CircuitBreaker
{

    /**
     * @var
     */
    public $config = [];

    /**
     * @var EventDispatcherInterface
     */
    public $dispatcher;

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
        // break
        if ($runtime->status == CommandRuntime::STATUS_OPEN) {
            if (CommandRuntime::microtime() - $runtime->opentime >= $command->getSleepWindow()) {
                $runtime->status(CommandRuntime::STATUS_CLOSE);
                $this->dispatch($command);
            }
            return call_user_func($fallback);
        }
        $successVolume = count($runtime->sampling["success"] ?? []);
        $errorVolume   = count($runtime->sampling["error"] ?? []);
        $requestVolume = $successVolume + $errorVolume;
        if ($requestVolume >= $command->getRequestVolumeThreshold()) {
            $errorPercent      = $errorVolume / $requestVolume;
            $runtime->sampling = [];
            if ($errorPercent >= $command->getErrorPercentThreshold() / 100) {
                $runtime->status(CommandRuntime::STATUS_OPEN);
                $this->dispatch($command);
                return call_user_func($fallback);
            } else {
                $runtime->status(CommandRuntime::STATUS_CLOSE);
                $this->dispatch($command);
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
            $starttime = CommandRuntime::microtime();
            $result    = call_user_func($request);
            if (CommandRuntime::microtime() - $starttime >= $command->getTimeout()) {
                throw new TimeoutException('Call timeout');
            }
            $runtime->sampling["success"][] = $id;
        } catch (TimeoutException $ex) {
            $runtime->sampling["error"][] = $id;
        } catch (\Throwable $ex) {
            $runtime->sampling["error"][] = $id;
            throw $ex;
        } finally {
            unset($runtime->currentRequests[$id]);
        }
        return $result;
    }

    /**
     * Dispatch
     * @param Command $command
     */
    protected function dispatch(Command $command)
    {
        if (!isset($this->dispatcher)) {
            return;
        }
        $event         = new StatusChangeEvent();
        $event->name   = $command->getName();
        $event->status = $command->getRuntime()->status;
        $this->dispatcher->dispatch($event);
    }

}
