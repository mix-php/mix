<?php

namespace Mix\Micro\Etcd;

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
     * Timeout
     * @var int
     */
    public $timeout = 5;

    /**
     * @var string
     */
    public $namespace = '/micro/config';

    /**
     * @var EventDispatcherInterface
     */
    public $dispatcher;

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var callable
     */
    protected $listenCallback;

    /**
     * @var Timer
     */
    protected $listenTimer;

    /**
     * Configurator constructor.
     * @param string $url
     * @param string $user
     * @param string $password
     * @param int $timeout
     */
    public function __construct(string $url, string $user, string $password, int $timeout = 5)
    {
        $this->url      = $url;
        $this->user     = $user;
        $this->password = $password;
        $this->timeout  = $timeout;
        $this->client   = $this->createClient();
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
     * Listen
     * @throws \RuntimeException
     * @throws \GuzzleHttp\Exception\BadResponseException
     */
    public function listen()
    {
        if (isset($this->listenTimer)) {
            throw new \RuntimeException('Already listening');
        }
        // 拉取全量
        $lastConfig = $this->all();
        foreach ($lastConfig as $key => $value) {
            $event        = new PutEvent();
            $event->key   = $key;
            $event->value = $value;
            $this->dispatcher->dispatch($event);
        }
        // 定时监听
        $timer    = Timer::new();
        $callback = function () use (&$lastConfig) {
            $config = $this->all();
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
        };
        $timer->tick($this->interval * 1000, $callback);
        $this->listenCallback = $callback;
        $this->listenTimer    = $timer;
    }

    /**
     * Sync to config-server
     * 可在 git webhook 中调用某个接口来触发该方法
     * @param string $path 目录或者文件路径
     * @param string $prefix
     */
    public function sync(string $path)
    {
        $config     = (new \Noodlehaus\Config($path))->all();
        $lastConfig = $this->all();
        // put
        $kvs = array_diff_assoc($config, $lastConfig);
        empty($kvs) or $this->put($kvs);
        // delete
        $keys = array_keys(array_diff_assoc($lastConfig, $config));
        empty($keys) or $this->delete($kvs);
        // call listen
        $this->listenCallback and call_user_func($this->listenCallback);
    }

    /**
     * Pull
     * @return string[]
     * @throws \GuzzleHttp\Exception\BadResponseException
     */
    public function all()
    {
        return $this->client->getKeysWithPrefix($this->namespace);
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
     * Delete
     * @param array $keys
     */
    public function delete(array $keys)
    {
        $client = $this->client;
        foreach ($keys as $key) {
            $client->del($key);
        }
    }

    /**
     * Close
     */
    public function close()
    {
        $this->listenTimer and $this->listenTimer->clear();
    }

}
