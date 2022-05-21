<?php

namespace App\Container;

/**
 * Class RDSLogger
 * @package App\Container
 */
class RDSLogger implements \Mix\Redis\LoggerInterface
{

    /**
     * @param float $time
     * @param string $cmd
     * @param array $args
     * @param \Throwable|null $exception
     * @return void
     */
    public function trace(float $time, string $cmd, array $args, ?\Throwable $exception): void
    {
        Logger::instance()->debug(sprintf('RDS: %sms %s %s', $time, $cmd, json_encode($args)));
    }

}