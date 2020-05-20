<?php

namespace Mix\Micro\Etcd;

use Mix\Micro\Etcd\Client\Client;
use Mix\Micro\Etcd\Exception\UnavailableException;
use Mix\Micro\Etcd\Factory\ServiceBundleFactory;
use Mix\Micro\Etcd\Factory\ServiceFactory;
use Mix\Micro\Etcd\LoadBalancer\LoadBalancerInterface;
use Mix\Micro\Etcd\LoadBalancer\RoundRobinBalancer;
use Mix\Micro\Etcd\Monitor\Monitor;
use Mix\Micro\Etcd\Register\Registrar;
use Mix\Micro\Register\Exception\NotFoundException;
use Mix\Micro\Register\Helper\ServiceHelper;
use Mix\Micro\Register\ServiceBundleInterface;
use Mix\Micro\Register\RegistryInterface;
use Mix\Micro\Register\ServiceInterface;

/**
 * Class Registry
 * @package Mix\Micro\Etcd
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
     * @var string
     */
    public $namespace = '/micro/registry';

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
    public $maxIdle = 30;

    /**
     * 负载均衡器
     * 默认为 RoundRobinBalancer
     * @var LoadBalancerInterface
     */
    public $loadBalancer;

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
     * Configurator constructor.
     * @param string $url
     * @param string $user
     * @param string $password
     * @param int $timeout
     */
    public function __construct(string $url, string $user = '', string $password = '', int $timeout = 5)
    {
        $this->url          = $url;
        $this->user         = $user;
        $this->password     = $password;
        $this->timeout      = $timeout;
        $this->client       = $this->createClient();
        $this->loadBalancer = $this->getDefaultLoadBalancer();
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
     * Default LoadBalancer
     * @return RoundRobinBalancer
     */
    protected function getDefaultLoadBalancer()
    {
        return new RoundRobinBalancer();
    }

    /**
     * Extract
     * @param \Mix\Micro\Options $options
     * @return ServiceInterface[]
     */
    public function extract(\Mix\Micro\Options $options)
    {
        $factory = new ServiceFactory();
        if ($options->server instanceof \Mix\Http\Server\Server && $options->router) {
            return $factory->createServiceFromHTTP($options->name, $options->server, $options->router, $options->version, $options->metadata);
        }
        if ($options->server instanceof \Mix\Grpc\Server) {
            return $factory->createServiceFromGrpc($options->server, $options->version, $options->metadata);
        }
        if ($options->server instanceof \Mix\JsonRpc\Server) {
            return $factory->createServiceFromJsonRpc($options->server, $options->version, $options->metadata);
        }
        return [];
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
            $monitor               = new Monitor($this->client, $this->monitors, $this->namespace, $name, $this->maxIdle);
            $this->monitors[$name] = $monitor;
        }
        $services = $this->monitors[$name]->services();
        return $this->loadBalancer->invoke($services);
    }

    /**
     * Register
     * @param ServiceInterface ...$services
     * @throws \InvalidArgumentException
     */
    public function register(ServiceInterface ...$services)
    {
        foreach ($services as $service) {
            $id = $service->getID();
            if (isset($this->registrars[$id])) {
                throw new \InvalidArgumentException(sprintf('Service %s repeated register', $id));
            }
            $registrar = new Registrar($this->client, $service, $this->namespace, $this->registerTTL);
            $registrar->register();
            $this->registrars[$id] = $registrar;
        }
    }

    /**
     * Deregister
     * @param ServiceInterface ...$service
     * @throws \InvalidArgumentException
     */
    public function deregister(ServiceInterface ...$service)
    {
        foreach ($services as $service) {
            $id = $service->getID();
            if (!isset($this->registrars[$id])) {
                throw new \InvalidArgumentException(sprintf('Service %s not registered', $id));
            }
            $this->registrars[$id]->deregister();
            unset($this->registrars[$id]);
        }
    }

    /**
     * Close
     * close all monitor
     * deregister all service
     */
    public function close()
    {
        foreach ($this->monitors as $monitor) {
            $monitor->close();
        }
        foreach ($this->registrars as $registrar) {
            $registrar->deregister();
        }
    }

}
