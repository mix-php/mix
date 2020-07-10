<?php

namespace Mix\Micro\Etcd\Monitor;

use Mix\Micro\Etcd\Client\Client;
use Mix\Micro\Etcd\Client\Watcher;
use Mix\Micro\Etcd\Service\Endpoint;
use Mix\Micro\Etcd\Service\Node;
use Mix\Micro\Etcd\Service\Request;
use Mix\Micro\Etcd\Service\Response;
use Mix\Micro\Etcd\Service\Service;
use Mix\Micro\Etcd\Service\Value;
use Mix\Micro\Register\Exception\NotFoundException;
use Mix\Time\Ticker;
use Mix\Time\Time;

/**
 * Class Monitor
 * @package Mix\Micro\Etcd\Monitor
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
     * @var Service[][]
     */
    protected $services = [];

    /**
     * @var Ticker
     */
    protected $ticker;

    /**
     * @var Watcher
     */
    protected $watcher;

    /**
     * @var string
     */
    protected $serviceFormat = '%s/%s/';

    /**
     * @var int
     */
    protected $lastTime = 0;

    /**
     * Monitor constructor.
     * @param Client $client
     * @param array $monitors
     * @param string $namespace
     * @param string $name
     * @param int $idle
     * @throws \Exception
     */
    public function __construct(Client $client, array &$monitors, string $namespace, string $name, int $idle)
    {
        $this->client        = $client;
        $this->monitors      = &$monitors;
        $this->serviceFormat = sprintf($this->serviceFormat, $namespace, '%s');
        $this->name          = $name;
        $this->idle          = $idle;
        $this->prefix        = $prefix = sprintf($this->serviceFormat, $name);

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
        $this->ticker = Time::newTicker(($this->idle * 1000) * Time::MILLISECOND);
        xgo(function () {
            while (true) {
                $ts = $this->ticker->channel()->pop();
                if (!$ts) {
                    return;
                }
                // 超过 4/5 的生存时间没有获取服务就停止监听器
                if (time() - $this->lastTime > $this->idle / 5 * 4) {
                    unset($this->monitors[$this->name]);
                    $this->close();
                    continue;
                }
                $this->pull();
            }
        });
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
            $data['name'],
            $data['version']
        );

        foreach ($data['metadata'] ?? [] as $k => $v) {
            $service->withMetadata($k, $v);
        }

        foreach ($data['endpoints'] ?? [] as $v) {
            $request = new Request($v['request']['name'], $v['request']['type']);
            foreach ($v['request']['values'] ?? [] as $vv) {
                $requestValue = new Value($vv['name'], $vv['type']);
                $request->withValue($requestValue);
            }

            $response = new Response($v['response']['name'], $v['response']['type']);
            foreach ($v['response']['values'] ?? [] as $vv) {
                $responseValue = new Value($vv['name'], $vv['type']);
                $response->withValue($responseValue);
            }

            $endpoint = new Endpoint($v['name'], $request, $response);
            foreach ($v['metadata'] ?? [] as $kk => $vv) {
                $endpoint->withMetadata($kk, $vv);
            }
            $service->withEndpoint($endpoint);
        }

        $rawNode = $data['nodes'][0];
        $node    = new Node($rawNode['id'], $rawNode['address']);
        foreach ($rawNode['metadata'] ?? [] as $k => $v) {
            $node->withMetadata($k, $v);
        }
        $service->withNode($node);

        return $service;
    }

    /**
     * Get all service
     * @return Service[]
     * @throws NotFoundException
     */
    public function services()
    {
        $first          = !$this->lastTime;
        $this->lastTime = time();

        $name     = $this->name;
        $services = $this->services[$name] ?? [];
        if (empty($services)) {
            // 第一次获取就找不到，必须关闭监听器，防止恶意404攻击导致注册中心连接数过高
            if ($first) {
                unset($this->monitors[$this->name]);
                $this->close();
            }
            throw new NotFoundException(sprintf('Service %s not found', $name));
        }

        return $services;
    }

    /**
     * Round robin get service
     * @return Service
     * @throws NotFoundException
     */
    public function roundRobin(): Service
    {
        $services = $this->services();
        return $services[array_rand($services)];
    }

    /**
     * Close
     */
    public function close()
    {
        $this->watcher->close();
        $this->ticker->stop();
    }

}
