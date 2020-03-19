<?php

namespace Mix\Etcd;

use Mix\Concurrent\Timer;
use Mix\Etcd\Client\Client;
use Mix\Micro\Event\DeleteEvent;
use Mix\Micro\Event\PutEvent;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * Class Config
 * @package Mix\Etcd
 */
class Config
{

    /**
     * Host
     * @var string
     */
    public $host = '127.0.0.1';

    /**
     * Port
     * @var int
     */
    public $port = 2379;

    /**
     * User
     * @var string
     */
    public $user = '';

    /**
     * Password
     * @var string
     */
    public $password = '';

    /**
     * 刷新配置间隔时间
     * @var int
     */
    public $interval = 5;

    /**
     * 接入的名称空间
     * @var string[]
     */
    public $namespaces = [
        '/app',
    ];

    /**
     * @var EventDispatcherInterface
     */
    public $dispatcher;

    /**
     * Version
     * @var string
     */
    protected $version = 'v3';

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var Timer
     */
    protected $timer;

    /**
     * @var string[]
     */
    protected $lastConfig = [];

    /**
     * Create Client
     * @return Client
     */
    protected function createClient()
    {
        $client = new Client(sprintf('%s:%d', $this->host, $this->port), $this->version);
        $client->setPretty(true);
        $token = $client->authenticate($this->user, $this->password);
        if (is_string($token)) {
            $client->setToken($token);
        }
        return $client;
    }

    /**
     * Pull config
     * @return string[]
     * @throws \GuzzleHttp\Exception\BadResponseException
     */
    public function pull()
    {
        if (!isset($this->client)) {
            $this->client = $this->createClient();
        }
        $client = $this->client;
        $config = [];
        foreach ($this->namespaces as $namespace) {
            $kvs = $client->getKeysWithPrefix($prefix);
            if (empty($kvs)) {
                continue;
            }
            $config = array_merge($config, $kvs);
        }
        return $config;
    }

    /**
     * 监听配置变化
     */
    public function listen()
    {
        $timer = $this->timer = Timer::new();
        $timer->tick($this->interval * 1000, function () {
            $config     = $this->pull();
            $lastConfig = $this->lastConfig;
            $diff       = array_diff_assoc($lastConfig, $config);
            foreach ($diff as $key => $value) {
                // delete
                if (isset($lastConfig[$key]) && !isset($config[$key])) {
                    $event      = new DeleteEvent();
                    $event->key = $key;
                    $this->dispatcher->dispatch($event);
                    continue;
                }
                // put
                $event        = new PutEvent();
                $event->key   = $key;
                $event->value = $value;
                $this->dispatcher->dispatch($event);
            }
        });
    }

    /**
     * Close
     */
    public function close()
    {
        $this->timer and $this->timer->clear();
    }

}
