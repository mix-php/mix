<?php

namespace Mix\Database;

/**
 * Class EmptyDriver
 * @package Mix\Database
 */
class EmptyDriver
{
    protected $errorMessage = 'The connection has been returned to the pool, the current operation cannot be performed';

    public function __construct()
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

    public function connect()
    {
        throw new \RuntimeException($this->errorMessage);
    }

    public function close()
    {
        throw new \RuntimeException($this->errorMessage);
    }
}
