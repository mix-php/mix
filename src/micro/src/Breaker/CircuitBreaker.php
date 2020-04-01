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
     * @var string[]
     */
    protected $currentRequests = [];

    /**
     * @var array
     */
    protected $sampling = [];

    /**
     * 状态值
     */
    const STATUS_OPEN = 1;
    const STATUS_CLOSE = 0;

    /**
     * @var int
     */
    protected $status = self::STATUS_CLOSE;

    /**
     * @var int
     */
    protected $opentime = 0;

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
            $definition         = new CommandDefinition($item);
            $name               = $definition->getName();
            $definitions[$name] = $definition;
        }
        $this->definitions = $definitions;
    }

    /**
     * 获取 CommandDefinition
     * @param string $name
     * @return CommandDefinition
     * @throws NotFoundException
     */
    public function getCommandDefinition(string $commandName): CommandDefinition
    {
        if (!isset($this->definitions[$commandName])) {
            throw new NotFoundException("Command definition '{$commandName}' not found");
        }
        return $this->definitions[$commandName];
    }

    /**
     * Do
     * @param string $name
     * @param \Closure $request
     * @param \Closure $fallback
     * @throws NotFoundException
     */
    public function do(string $commandName, \Closure $request, \Closure $fallback)
    {
        $definition = $this->getCommandDefinition($commandName);
        // fuse
        if ($this->status == static::STATUS_OPEN) {
            if (static::microtime() - $this->opentime >= $definition->getSleepWindow()) {
                $this->status(static::STATUS_CLOSE);
            }
            return call_user_func($fallback);
        }
        if (count($this->sampling) >= $definition->getRequestVolumeThreshold()) {
            $errorPercent   = count($this->sampling["error"] ?? []) / count($this->sampling["success"] ?? []);
            $this->sampling = [];
            if ($errorPercent >= $definition->getErrorPercentThreshold() / 100) {
                $this->status(static::STATUS_OPEN);
                return call_user_func($fallback);
            } else {
                $this->status(static::STATUS_CLOSE);
            }
        }
        // concurrent
        if ($definition->getMaxConcurrentRequests() && count($this->currentRequests) + 1 >= $definition->getMaxConcurrentRequests()) {
            return call_user_func($fallback);
        }
        $id                         = spl_object_hash($request);
        $this->currentRequests[$id] = $request;
        // call
        try {
            call_user_func($request);
            $this->sampling["success"] = $id;
        } catch (\Throwable $ex) {
            $this->sampling["error"] = $id;
            throw $ex;
        } finally {
            unset($this->currentRequests[$id]);
        }
    }

    /**
     * Set status
     * @param int $status
     */
    protected function status(int $status)
    {
        if ($status == static::STATUS_OPEN) {
            $this->opentime = static::microtime();
            $this->status   = static::STATUS_OPEN;
            return;
        }
        $this->opentime = 0;
        $this->status   = static::STATUS_CLOSE;
    }

    /**
     * 获取当前时间, 单位: 秒, 粒度: 微秒
     * @return float
     */
    protected static function microtime()
    {
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }

}
