<?php

namespace Mix\Etcd;

use Mix\Bean\BeanInjector;
use Mix\Etcd\Client\Client;
use Mix\Etcd\Monitor\Monitor;
use Mix\Etcd\Register\Registrar;
use Mix\Micro\Exception\NotFoundException;
use Mix\Micro\ServiceBundleInterface;
use Mix\Micro\RegistryInterface;
use Mix\Micro\ServiceInterface;

/**
 * Class Registry
 * @package Mix\Etcd
 */
class Registry implements RegistryInterface
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
     * Registrar keep alive TTL
     * 注册器生存时间，会根据该时间定时延期服务的有效期
     * @var int
     */
    public $ttl = 5;

    /**
     * Monitor idle time
     * 监控最大空闲时间，超过该时间将自动关闭
     * @var int
     */
    public $idle = 30;

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
     * 注册器集合
     * @var Registrar[]
     */
    protected $registrars = [];

    /**
     * 服务监控集合
     * @var Monitor[]
     */
    protected $monitors = [];
    
    /**
     * Registry constructor.
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
     * Get Service
     * @param string $name
     * @return ServiceInterface
     * @throws NotFoundException
     */
    public function get(string $name): ServiceInterface
    {
        if (!isset($this->monitors[$name])) {
            $monitor               = new Monitor($this->client, $this->monitors, $name, $this->idle);
            $this->monitors[$name] = $monitor;
        }
        return $this->monitors[$name]->random();
    }

    /**
     * Register
     * @param ServiceBundleInterface $bundle
     * @throws \Exception
     */
    public function register(ServiceBundleInterface $bundle)
    {
        $id = spl_object_hash($bundle);
        if (isset($this->registrars[$id])) {
            throw new \Exception(sprintf('Repeat register service, bundle id: %s', $id));
        }
        if ($bundle->count() == 0) {
            return;
        }
        $registrar = new Registrar($this->client, $bundle, $this->ttl);
        $registrar->register();
        $this->registrars[$id] = $registrar;
    }

    /**
     * Un Register
     * @param ServiceBundleInterface $bundle
     * @throws \Exception
     */
    public function unregister(ServiceBundleInterface $bundle)
    {
        $id = spl_object_hash($bundle);
        if (!isset($this->registrars[$id])) {
            throw new \Exception(sprintf('Unregister service failed, bundle id: %s', $id));
        }
        $this->registrars[$id]->unregister();
        unset($this->registrars[$id]);
    }

    /**
     * Close
     * close all monitor
     * unregister all service
     */
    public function close()
    {
        foreach ($this->monitors as $monitor) {
            $monitor->close();
        }
        foreach ($this->registrars as $registrar) {
            $registrar->unregister();
        }
    }

}
