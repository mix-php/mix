<?php

namespace Mix\Micro\Etcd;

use Mix\Micro\Etcd\Client\Client;
use Mix\Micro\Config\ConfigInterface;
use Mix\Micro\Config\Event\DeleteEvent;
use Mix\Micro\Config\Event\PutEvent;
use Mix\Time\Ticker;
use Mix\Time\Time;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * Class Config
 * @package Mix\Micro\Etcd
 */
class Config implements ConfigInterface
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
     * @var int
     */
    public $interval = 5;

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var callable
     */
    protected $listenCallback;

    /**
     * @var Ticker
     */
    protected $listenTicker;

    /**
     * Configurator constructor.
     * @param string $url
     * @param string $user
     * @param string $password
     * @param int $timeout
     */
    public function __construct(string $url, string $user = '', string $password = '', int $timeout = 5)
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
     * @param EventDispatcherInterface $dispatcher
     * @throws \RuntimeException
     * @throws \GuzzleHttp\Exception\BadResponseException
     */
    public function listen(EventDispatcherInterface $dispatcher)
    {
        if (isset($this->listenTicker)) {
            throw new \RuntimeException('Already listening');
        }
        if (!$dispatcher) {
            throw new \RuntimeException('Property dispatcher cannot be empty');
        }
        // 拉取全量
        $lastConfig = $this->all();
        foreach ($lastConfig as $key => $value) {
            $event        = new PutEvent();
            $event->key   = $key;
            $event->value = $value;
            $dispatcher->dispatch($event);
        }
        // 定时监听
        $ticker   = Time::newTicker(($this->interval * 1000) * Time::MILLISECOND);
        $callback = function () use (&$lastConfig, $dispatcher) {
            $config = $this->all();
            // put
            $putKvs = array_diff_assoc($config, $lastConfig);
            foreach ($putKvs as $key => $value) {
                $event        = new PutEvent();
                $event->key   = $key;
                $event->value = $value;
                $dispatcher->dispatch($event);
            }
            // delete
            $deleteKvs = array_diff_assoc($lastConfig, $config);
            foreach ($deleteKvs as $key => $value) {
                if (isset($putKvs[$key])) { // 如果同一个 key put/delete 都有，只处理 put
                    continue;
                }
                $event      = new DeleteEvent();
                $event->key = $key;
                $dispatcher->dispatch($event);
            }
        };
        xgo(function () use ($ticker, $callback) {
            while (true) {
                $ts = $ticker->channel()->pop();
                if (!$ts) {
                    return;
                }
                call_user_func($callback);
            }
        });
        $this->listenCallback = $callback;
        $this->listenTicker   = $ticker;
    }

    /**
     * Sync
     * @param string $path 目录或者文件路径
     */
    public function sync(string $path)
    {
        $config = [];
        foreach ((new \Noodlehaus\Config($path))->all() as $key => $value) {
            $config[sprintf('%s%s', $this->namespace, $key)] = $value;
        }
        $lastConfig = $this->all();
        // put
        $putKvs = array_diff_assoc($config, $lastConfig);
        empty($putKvs) or $this->put($putKvs);
        // delete
        $deleteKeys = [];
        foreach (array_keys(array_diff_assoc($lastConfig, $config)) as $key) {
            if (isset($putKvs[$key])) { // 如果同一个 key put/delete 都有，只处理 put
                continue;
            }
            $deleteKeys[] = $key;
        }
        empty($deleteKeys) or $this->delete($deleteKeys);
        // call listen
        if ($this->listenCallback && (!empty($putKvs) || !empty($deleteKeys))) {
            call_user_func($this->listenCallback);
        }
    }

    /**
     * Get
     * @param string $key
     * @param string $default
     * @return string
     */
    public function get(string $key, string $default = ''): string
    {
        $kv = $this->client->get($key);
        if (empty($kv)) {
            return $default;
        }
        return array_pop($kv);
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
    protected function put(array $kvs)
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
    protected function delete(array $keys)
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
        $this->listenTicker and $this->listenTicker->stop();
    }

}
