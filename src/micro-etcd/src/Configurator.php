<?php

namespace Mix\Micro\Etcd;

use Mix\Bean\BeanInjector;
use Mix\Concurrent\Timer;
use Mix\Micro\Etcd\Client\Client;
use Mix\Micro\Config\ConfiguratorInterface;
use Mix\Micro\Config\Event\DeleteEvent;
use Mix\Micro\Config\Event\PutEvent;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * Class Configurator
 * @package Mix\Micro\Etcd
 */
class Configurator implements ConfiguratorInterface
{

    /**
     * Url
     * @var string
     */
    public $url = 'http://127.0.0.1:2379/v3';

    /**
     * Timeout
     * @var int
     */
    public $timeout = 5;

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
     * 配置刷新间隔时间，单位：秒
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
     * 事件调度器
     * @var EventDispatcherInterface
     */
    public $dispatcher;

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var Timer[]
     */
    protected $timers = [];

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
        $client = new Client($this->url, $this->timeout);
        $client->auth($this->user, $this->password);
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
        $timer = Timer::new();
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
        $this->timers[] = $timer;
    }

    /**
     * 同步文件配置到配置中心
     * @param string $path 目录或者文件路径
     */
    public function sync(string $path)
    {
        // 定时同步
        $timer = Timer::new();
        $timer->tick($this->interval * 1000, function () use ($path) {
            $noodlehaus = new \Noodlehaus\Config($path);
            $kvs        = $noodlehaus->all();
            $this->put($kvs);
        });
        $this->timers[] = $timer;
    }

    /**
     * Close
     */
    public function close()
    {
        foreach ($this->timers as $timer) {
            $timer->clear();
        }
    }

}
