<?php

namespace Mix\Micro\Hystrix;

/**
 * Class Command
 * @package Mix\Micro\Hystrix
 */
class Command
{

    /**
     * 命令名称
     * @var string
     */
    protected $name;

    /**
     * 超时时间, 单位: 秒
     * @var float
     */
    protected $timeout = 5.0;

    /**
     * 最大并发数，超过并发返回错误, 0=关闭
     * @var int
     */
    protected $maxConcurrentRequests = 5;

    /**
     * 请求数量的阀值，用这些数量的请求来计算阀值
     * @var int
     */
    protected $requestVolumeThreshold = 4;

    /**
     * 错误百分比阀值，达到阀值，启动熔断
     * @var int
     */
    protected $errorPercentThreshold = 25;

    /**
     * 熔断尝试恢复时间, 单位: 秒
     * @var float
     */
    protected $sleepWindow = 10.0;

    /**
     * @var CommandRuntime
     */
    protected $runtime;

    /**
     * CommandDefinition constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        foreach ($config as $key => $value) {
            $this->$key = $value;
        }
        $this->runtime = new CommandRuntime();
    }

    /**
     * Get name
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get timeout
     * @return float
     */
    public function getTimeout(): float
    {
        return $this->timeout;
    }

    /**
     * Set timeout
     * @param float $timeout
     */
    public function withTimeout(float $timeout)
    {
        $this->timeout = $timeout;
    }

    /**
     * Get max concurrent requests
     * @return int
     */
    public function getMaxConcurrentRequests(): int
    {
        return $this->maxConcurrentRequests;
    }

    /**
     * Set max concurrent requests
     * @param int $requests
     */
    public function withMaxConcurrentRequests(int $requests)
    {
        $this->maxConcurrentRequests = $requests;
    }

    /**
     * Get request volume threshold
     * @return int
     */
    public function getRequestVolumeThreshold(): int
    {
        return $this->requestVolumeThreshold;
    }

    /**
     * Set request volume threshold
     * @param int $threshold
     */
    public function withRequestVolumeThreshold(int $threshold)
    {
        $this->requestVolumeThreshold = $threshold;
    }

    /**
     * Get error percent threshold
     * @return int
     */
    public function getErrorPercentThreshold(): int
    {
        return $this->errorPercentThreshold;
    }

    /**
     * Set error percent threshold
     * @return int
     */
    public function withErrorPercentThreshold(int $threshold)
    {
        $this->errorPercentThreshold = $threshold;
    }

    /**
     * Get sleep window
     * @return float
     */
    public function getSleepWindow(): float
    {
        return $this->sleepWindow;
    }

    /**
     * Set sleep window
     * @return float
     */
    public function withSleepWindow(float $sleepWindow)
    {
        return $this->sleepWindow = $sleepWindow;
    }

    /**
     * @return CommandRuntime
     */
    public function getRuntime()
    {
        return $this->runtime;
    }

}
