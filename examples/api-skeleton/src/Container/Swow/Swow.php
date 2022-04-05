<?php

namespace App\Container\Swow;

use Psr\Log\LoggerInterface;
use Swow\Socket;

class Swow extends Socket
{
    /**
     * @var string|null
     */
    public $host = null;

    /**
     * @var int|null
     */
    public $port = null;

    /**
     * @var callable
     */
    protected $handler;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param LoggerInterface $logger
     * @param int $type
     */
    public function __construct(LoggerInterface $logger, int $type = Socket::TYPE_TCP)
    {
        parent::__construct($type);
    }

    /**
     * @param string $name
     * @param int $port
     * @param int $flags
     * @return static
     */
    public function bind(string $name, int $port = 0, int $flags = Socket::BIND_FLAG_NONE): static
    {
        $this->host = $name;
        $this->port = $port;
        parent::bind($name, $port, $flags);
        return $this;
    }

    public function handle(callable $callable)
    {
        $this->handler = $callable;
        return $this;
    }

    public function start()
    {
        $this->listen();
        while (true) {
            Coroutine::create($this->handler, $this->accept());
        }
    }
}