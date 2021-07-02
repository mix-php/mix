<?php

namespace Mix\Database;

/**
 * Interface LoggerInterface
 * @package Mix\Database
 */
interface LoggerInterface
{

    public function trace(float $time, string $sql, array $bindings, int $rowCount, ?\Throwable $exception);

}