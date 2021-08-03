<?php

namespace App\Container;

/**
 * Class RDSLogger
 * @package App\Container
 */
class RDSLogger implements \Mix\Redis\LoggerInterface
{

    public function trace(float $time, string $cmd, array $args, ?\Throwable $exception): void
    {
        Logger::instance()->debug(sprintf('RDS: %sms %s %s', $time, $cmd, json_encode($args)));
    }

}