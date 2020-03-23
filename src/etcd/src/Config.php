<?php

namespace Mix\Etcd;

use Mix\Bean\BeanInjector;
use Mix\Concurrent\Timer;
use Mix\Etcd\Client\Client;
use Mix\Micro\Config\Event\DeleteEvent;
use Mix\Micro\Config\Event\PutEvent;
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
     * Config constructor.
     * @param array $config
     * @throws \PhpDocReader\AnnotationException
     * @throws \ReflectionException
     */
    public function __construct(array $config = [])
    {
        BeanInjector::inject($this, $config);
    }

    /**
     * Init
     * @return void
     */
    public function init()
    {
        $this->client = $this->createClient();
    }

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
     * Put
     * @param array $kvs
     * @throws \GuzzleHttp\Exception\BadResponseException
     */
    public function put(array $kvs)
    {
        $client = $this->client;
        foreach ($kvs as $key => $value) {
            $client->put($key, $value);
        }
    }

    /**
     * Pull config
     * @return string[]
     * @throws \GuzzleHttp\Exception\BadResponseException
     */
    public function pull()
    {
        $client = $this->client;
        $config = [];
        foreach ($this->namespaces as $namespace) {
            $kvs = $client->getKeysWithPrefix($namespace);
            if (empty($kvs)) {
                continue;
            }
            $config = array_merge($config, $kvs);
        }
        return $config;
    }

    /**
     * 监听配置变化
     * @throws \GuzzleHttp\Exception\BadResponseException
     */
    public function listen()
    {
        // 拉取全量
        $config           = $this->pull();
        $this->lastConfig = $config;
        foreach ($config as $key => $value) {
            $event        = new PutEvent();
            $event->key   = $key;
            $event->value = $value;
            $this->dispatcher->dispatch($event);
        }
        // 定时监听
        $timer = $this->timer = Timer::new();
        $timer->tick($this->interval * 1000, function () {
            $config           = $this->pull();
            $lastConfig       = $this->lastConfig;
            $this->lastConfig = $config;
            foreach (array_diff_assoc($config, $lastConfig) as $key => $value) {
                // put
                $event        = new PutEvent();
                $event->key   = $key;
                $event->value = $value;
                $this->dispatcher->dispatch($event);
            }
            foreach (array_diff_assoc($lastConfig, $config) as $key => $value) {
                // delete
                if (!isset($config[$key])) {
                    $event      = new DeleteEvent();
                    $event->key = $key;
                    $this->dispatcher->dispatch($event);
                    continue;
                }
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
