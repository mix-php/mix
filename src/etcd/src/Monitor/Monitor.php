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
     * @var string
     */
    public $name = '';

    /**
     * @var string
     */
    protected $prefix = '';

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
    protected $serviceFormat = '/mix/service/%s/';

    /**
     * Monitor constructor.
     * @param Client $client
     * @param string $name
     * @throws \Exception
     */
    public function __construct(Client $client, string $name)
    {
        $this->client = $client;
        $this->name   = $name;
        $this->prefix = $prefix = sprintf($this->serviceFormat, $name);

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
        $name     = $this->name;
        $services = $this->services[$name] ?? [];
        if (empty($services)) {
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
