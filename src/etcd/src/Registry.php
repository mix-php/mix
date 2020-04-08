<?php

namespace Mix\Etcd;

use Mix\Bean\BeanInjector;
use Mix\Etcd\Client\Client;
use Mix\Etcd\LoadBalancer\LoadBalancerInterface;
use Mix\Etcd\LoadBalancer\RoundRobinBalancer;
use Mix\Etcd\Monitor\Monitor;
use Mix\Etcd\Register\Registrar;
use Mix\Micro\Register\Exception\NotFoundException;
use Mix\Micro\Register\ServiceBundleInterface;
use Mix\Micro\Register\RegistryInterface;
use Mix\Micro\Register\ServiceInterface;

/**
 * Class Registry
 * @package Mix\Etcd
 */
class Registry implements RegistryInterface
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
     * Registrar keep alive TTL
     * 注册器生存时间，会根据该时间定时延期服务的有效期
     * @var int
     */
    public $registerTTL = 5;

    /**
     * Monitor idle time
     * 监控最大空闲时间，超过该时间将自动关闭
     * @var int
     */
    public $monitorMaxIdle = 30;

    /**
     * 负载均衡器
     * 默认为 RoundRobinBalancer
     * @var LoadBalancerInterface
     */
    public $loadBalancer;

    /**
     * Version
     * 只支持 v3，因为 Watcher 使用的 v3 接口
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
        // 创建默认负载均衡器
        if (!$this->loadBalancer) {
            $this->loadBalancer = new RoundRobinBalancer();
        }
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
     * Get Service
     * @param string $name
     * @return ServiceInterface
     * @throws NotFoundException
     */
    public function service(string $name): ServiceInterface
    {
        if (!isset($this->monitors[$name])) {
            $monitor               = new Monitor($this->client, $this->monitors, $name, $this->monitorMaxIdle);
            $this->monitors[$name] = $monitor;
        }
        $services = $this->monitors[$name]->services();
        return $this->loadBalancer->invoke($services);
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
        $registrar = new Registrar($this->client, $bundle, $this->registerTTL);
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
