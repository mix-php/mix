<?php

namespace Mix\Etcd\Service;

use Etcd\Client;
use Mix\Concurrent\Timer;

/**
 * Class ServiceMonitor
 * @package Mix\Etcd\Service
 */
class ServiceMonitor
{

    /**
     * @var string
     */
    public $name = '';

    /**
     * @var Client
     */
    public $client;

    /**
     * @var int
     */
    public $ttl;

    /**
     * @var Service[]
     */
    protected $services = [];

    /**
     * @var Timer
     */
    protected $timer;

    /**
     * ServiceMonitor constructor.
     * @param Client $client
     * @param string $name
     * @param int $ttl
     * @throws \Exception
     */
    public function __construct(Client $client, string $name, int $ttl)
    {
        $this->client = $client;
        $this->name   = $name;
        $this->ttl    = $ttl;
        $this->pull();
        $timer = Timer::new();
        $timer->tick($ttl * 1000 / 5 * 4, function () {
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
        $client         = $this->client;
        $name           = $this->name;
        $this->services = [];
        $result         = $client->getKeysWithPrefix(sprintf('/service/%s/', $name));
        if (!isset($result['count']) || $result['count'] == 0) {
            return;
        }
        $kvs = $result['kvs'];
        foreach ($kvs as $kv) {
            $value   = $kv['value'];
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
            $this->services[] = $service;
        }
    }

    /**
     * Random get service
     * @return Service
     * @throws \Exception
     */
    public function random(): Service
    {
        if (count($this->services) == 0) {
            throw new \Exception(sprintf('Service not found, name: %s', $this->name));
        }
        return $this->services[array_rand($this->services)];
    }

    /**
     * Close
     */
    public function close()
    {
        $this->timer->clear();
    }

}
