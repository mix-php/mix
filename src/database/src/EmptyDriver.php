<?php

namespace Mix\Database;

/**
 * Class EmptyDriver
 * @package Mix\Database
 */
class EmptyDriver extends Driver
{

    protected $errorMessage = 'The connection has been returned to the pool, the current operation cannot be performed';

    public function __construct(string $dsn = '', string $username = '', string $password = '', array $options = [])
    {
    }

    public function instance(): \PDO
    {
        throw new \RuntimeException($this->errorMessage);
    }

    public function options(): array
    {
        throw new \RuntimeException($this->errorMessage);
    }

    public function connect(): void
    {
        throw new \RuntimeException($this->errorMessage);
    }

    public function close(): void
    {
        throw new \RuntimeException($this->errorMessage);
    }

}
