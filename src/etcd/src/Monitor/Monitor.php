<?php

namespace Mix\Etcd\Monitor;

use Mix\Concurrent\Timer;
use Mix\Etcd\Client\Client;
use Mix\Etcd\Client\Watcher;
use Mix\Etcd\Service\Service;
use Mix\Micro\Exception\NotFoundException;

/**
 * Class Monitor
 * @package Mix\Etcd\Monitor
 */
class Monitor
{

    /**
     * @var Client
     */
    public $client;

    /**
     * @var Monitor[]
     */
    public $monitors = [];

    /**
     * @var string
     */
    public $name = '';

    /**
     * @var int second
     */
    public $idle = 0;

    /**
     * @var string
     */
    protected $prefix = '';

    /**
     * @var Service[name][id]
     */
    protected $services = [];

    /**
     * @var Timer
     */
    protected $timer;

    /**
     * @var Watcher
     */
    protected $watcher;

    /**
     * @var string
     */
    protected $serviceFormat = '/mix/service/%s/';

    /**
     * @var int
     */
    protected $lastTime = 0;

    /**
     * Monitor constructor.
     * @param Client $client
     * @param array $monitors
     * @param string $name
     * @param int $idle
     * @throws \Exception
     */
    public function __construct(Client $client, array &$monitors, string $name, int $idle)
    {
        $this->client   = $client;
        $this->monitors = &$monitors;
        $this->name     = $name;
        $this->idle     = $idle;
        $this->prefix   = $prefix = sprintf($this->serviceFormat, $name);

        $func = function (array $data) {
            if (!isset($data['result']['events'])) {
                return;
            }
            $events = $data['result']['events'];
            foreach ($events as $event) {
                $type = $event['type'] ?? 'PUT';
                $kv   = $event['kv'];
                switch ($type) {
                    case 'DELETE':
                        $key      = base64_decode($kv['key']);
                        $segments = explode('/', $key);
                        $id       = array_pop($segments);
                        $name     = array_pop($segments);
                        unset($this->services[$name][$id]);
                        break;
                    case 'PUT':
                        $value                                                  = base64_decode($kv['value']);
                        $service                                                = static::parseValue($value);
                        $this->services[$service->getName()][$service->getID()] = $service;
                        break;
                }
            }
        };

        $watcher = $client->watchKeysWithPrefix($prefix, $func);
        $watcher->forever();
        $this->watcher = $watcher;

        $this->pull();
        $timer = Timer::new();
        $timer->tick($this->idle * 1000, function () {
            // 超过 4/5 的生存时间没有获取服务就停止监听器
            if (time() - $this->lastTime > $this->idle / 5 * 4) {
                unset($this->monitors[$this->name]);
                $this->close();
                return;
            }
            $this->pull();
        });
        $this->timer = $timer;
    }

    /**
     * Pull service
     * @throws \GuzzleHttp\Exception\BadResponseException
     */
    public function pull()
    {
        $client = $this->client;
        $prefix = $this->prefix;
        $kvs    = $client->getKeysWithPrefix($prefix);
        if (empty($kvs)) {
            return;
        }
        $services = [];
        foreach ($kvs as $value) {
            $service                                          = static::parseValue($value);
            $services[$service->getName()][$service->getID()] = $service;
        }
        $this->services = $services;
    }

    /**
     * Parse value to service
     * @param string $value
     * @return Service
     */
    protected static function parseValue(string $value)
    {
        $data    = json_decode($value, true);
        $service = new Service(
            $data['id'],
            $data['name'],
            $data['address'],
            $data['port']
        );
        foreach ($data['metadata'] as $key => $value) {
            $service->withMetadata($key, $value);
        }
        $service->withNode($data['node']['id'], $data['node']['name']);
        return $service;
    }

    /**
     * Random get service
     * @return Service
     * @throws NotFoundException
     */
    public function random(): Service
    {
        $first          = !$this->lastTime;
        $this->lastTime = time();
        $name           = $this->name;
        $services       = $this->services[$name] ?? [];
        if (empty($services)) {
            // 第一次获取就找不到，必须关闭监听器，防止恶意404攻击导致注册中心连接数过高
            if ($first) {
                unset($this->monitors[$this->name]);
                $this->close();
            }
            throw new NotFoundException(sprintf('Service %s not found', $name));
        }
        return $services[array_rand($services)];
    }

    /**
     * Close
     */
    public function close()
    {
        $this->watcher->close();
        $this->timer->clear();
    }

}
