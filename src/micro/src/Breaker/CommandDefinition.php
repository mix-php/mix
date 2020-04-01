<?php

namespace Mix\Micro\Breaker;

/**
 * Class CommandDefinition
 * @package Mix\Micro\Breaker
 */
class CommandDefinition
{

    /**
     * 命令名称
     * @var string
     */
    protected $name;

    /**
     * 最大并发数，超过并发返回错误
     * 0 = 关闭
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
    protected $sleepWindow = 5.0;

    /**
     * CommandDefinition constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        foreach ($config as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getMaxConcurrentRequests(): int
    {
        return $this->maxConcurrentRequests;
    }

    /**
     * @return int
     */
    public function getRequestVolumeThreshold(): int
    {
        return $this->requestVolumeThreshold;
    }

    /**
     * @return int
     */
    public function getErrorPercentThreshold(): int
    {
        return $this->errorPercentThreshold;
    }

    /**
     * @return int
     */
    public function getSleepWindow(): int
    {
        return $this->sleepWindow;
    }

}
