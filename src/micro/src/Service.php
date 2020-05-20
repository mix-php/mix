<?php

namespace Mix\Micro;

use Mix\Concurrent\Timer;

/**
 * Class Service
 * @package Mix\Micro
 */
class Service
{

    /**
     * @var Options
     */
    protected $options;

    /**
     * @var Timer
     */
    protected $timer;

    /**
     * Service constructor.
     * @param \Closure ...$options
     */
    public function __construct(\Closure ...$options)
    {
        $this->options = new Options(...$options);
    }

    /**
     * Run
     * @throws \Swoole\Exception
     */
    public function run()
    {
        $timer = $this->timer = Timer::new();
        $timer->tick(100, function () use ($timer) {
            $server   = $this->options->server;
            $registry = $this->options->registry;
            $logger   = $this->options->logger;

            if (!$server->port) {
                return;
            }
            xdefer(function () use ($timer) {
                $timer->clear();
            });

            $services = $registry->extract($this->options);
            $registry->register(...$services);

            if ($logger) {
                $logger->info(sprintf('Server started [%s:%d]', $server->host, $server->port));
                foreach ($services as $service) {
                    $logger->info(sprintf('Register service [%s]', $service->getID()));
                }
            }
        });

        $this->options->server->start($this->options->router);
    }

    /**
     * destruct
     */
    public function __destruct()
    {
        $this->timer and $this->timer->clear();
    }

}
