<?php

namespace Mix\Micro;

use Mix\Signal\SignalNotify;
use Mix\Time\Ticker;
use Mix\Time\Time;

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
     * @var Ticker
     */
    protected $ticker;

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
        // 捕获信号
        if ($this->options->signal) {
            $notify = new SignalNotify(SIGHUP, SIGINT, SIGTERM);
            xgo(function () use ($notify) {
                $signal = $notify->channel()->pop();

                $logger   = $this->options->logger;
                $registry = $this->options->registry;
                $config   = $this->options->config;
                $server   = $this->options->server;
                if ($logger) {
                    $logger->info('Received signal [{signal}]', ['signal' => $signal]);
                    $logger->info('Server shutdown');
                }
                $registry and $registry->close();
                $config and $config->close();
                $server and $server->shutdown();

                $this->ticker and $this->ticker->stop();

                $notify->stop();
            });
        }

        // 服务注册
        $ticker = $this->ticker = Time::newTicker(100 * Time::MILLISECOND);
        xgo(function () use ($ticker) {
            xdefer(function () use ($ticker) {
                $ticker->stop();
            });
            while (true) {
                $ts = $ticker->channel()->pop();
                if (!$ts) {
                    return;
                }

                $server   = $this->options->server;
                $registry = $this->options->registry;
                $logger   = $this->options->logger;

                if (!$server->port()) {
                    continue;
                }

                $services = $registry->extract($this->options);
                $registry->register(...$services);

                if ($logger) {
                    $logger->info(sprintf('Server started [%s:%d]', $server->host(), $server->port()));
                    foreach ($services as $service) {
                        $logger->info(sprintf('Register service [%s]', $service->getID()));
                    }
                }

                return;
            }
        });

        $server = $this->options->server;
        if (!method_exists($server, 'start')) {
            throw new \BadMethodCallException('Server start method not found');
        }
        $server->start($this->options->router);
    }

}
