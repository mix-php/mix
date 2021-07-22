<?php

namespace Mix\Redis;

/**
 * Interface LoggerInterface
 * @package Mix\Redis
 */
interface LoggerInterface
{

    public function trace(float $time, string $cmd, array $args, ?\Throwable $exception): void;

}