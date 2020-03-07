<?php

namespace Mix\Etcd\Service;

use Mix\Concurrent\Timer;
use Mix\Etcd\Client\Client;
use Mix\Etcd\Client\Watcher;

/**
 * Class ServiceMonitor
 * @package Mix\Etcd\Service
 */
class ServiceMonitor
{

    /**
     * @var Client
     */
    public $client;

    /**
     * @var string
     */
    public $prefix = '';

    /**
     * @var Service[name][id]
     */
    protected $services = [];

    /**
     * @var int second
     */
    protected $interval = 60;

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
    protected $serviceFormat = 'mix/service/%s';

    /**
     * ServiceMonitor constructor.
     * @param Client $client
     * @param string $prefix
     * @throws \Exception
     */
    public function __construct(Client $client, string $prefix)
    {
        $this->client = $client;
        $this->prefix = $prefix;

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

        $watcher = $client->watchKeysWithPrefix(sprintf($this->serviceFormat, $prefix), $func);
        $watcher->forever();
        $this->watcher = $watcher;

        $this->pull();
        $timer = Timer::new();
        $timer->tick($this->interval * 1000, function () {
            $this->pull();
        });
        $this->timer = $timer;
    }

    /**
     * Pull service
     * @throws \Exception
     */
    public function pull()
    {
        $client = $this->client;
        $prefix = $this->prefix;
        $result = $client->getKeysWithPrefix(sprintf($this->serviceFormat, $prefix));
        if (!isset($result['count']) || $result['count'] == 0) {
            return;
        }
        $services = [];
        $kvs      = $result['kvs'];
        foreach ($kvs as $kv) {
            $value                                            = $kv['value'];
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
     * @param string $name
     * @return Service
     * @throws \Exception
     */
    public function random(string $name): Service
    {
        $services = $this->services[$name] ?? [];
        if (empty($services)) {
            throw new \Exception(sprintf('Service not found, name: %s', $name));
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
