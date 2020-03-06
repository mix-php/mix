<?php

namespace Mix\Etcd\Register;

use Mix\Etcd\Client\Client;
use Mix\Concurrent\Timer;
use Mix\Etcd\Exception\NotFoundException;
use Mix\Etcd\Node\Node;
use Mix\Etcd\Service\ServiceBundle;
use Mix\Micro\Helper\ServiceHelper;

/**
 * Class Registrar
 * @package Mix\Etcd\Register
 */
class Registrar
{

    /**
     * @var Client
     */
    public $client;

    /**
     * @var ServiceBundle
     */
    public $bundle;

    /**
     * @var int
     */
    public $ttl;

    /**
     * @var int
     */
    protected $leaseID;

    /**
     * @var Timer
     */
    protected $timer;

    /**
     * @var string
     */
    protected $nodeFormat = 'mix/node/%s/%s';

    /**
     * @var string
     */
    protected $serviceFormat = 'mix/service/%s/%s';

    /**
     * Registrar constructor.
     * @param Client $client
     * @param ServiceBundle $bundle
     * @param int $ttl
     */
    public function __construct(Client $client, ServiceBundle $bundle, int $ttl)
    {
        $this->client = $client;
        $this->bundle = $bundle;
        $this->ttl    = $ttl;
    }

    /**
     * Register
     * @throws \Exception
     */
    public function register()
    {
        $client  = $this->client;
        $bundle  = $this->bundle;
        $reslut  = $client->grant($this->ttl);
        $leaseID = $this->leaseID = (int)$reslut['ID'];
        $node    = new Node(ServiceHelper::uuid(), gethostname(), ServiceHelper::localIP());
        foreach ($bundle->items() as $service) {
            $node->withAddedService($service->getID(), $service->getName());
            $service->withNode($node->getID(), $node->getName());
            $client->put(sprintf($this->serviceFormat, $service->getName(), $service->getID()), json_encode($service), ['lease' => $leaseID]);
        }
        $client->put(sprintf($this->nodeFormat, $node->getName(), $node->getID()), json_encode($node), ['lease' => $leaseID]);
        $this->timer = $this->keepAlive();
    }

    /**
     * Un Register
     */
    public function unregister()
    {
        $this->timer->clear();
        $this->client->revoke($this->leaseID);
    }

    /**
     * Keep alive
     * @return Timer
     */
    protected function keepAlive()
    {
        $timer = Timer::new();
        $timer->tick($this->ttl * 1000 / 5 * 4, function () {
            try {
                // 当 lease 失效时，由于返回结果缺少 ttl，所以会抛出异常
                $this->client->keepAlive($this->leaseID);
            } catch (NotFoundException $ex) {
                $this->register();
            }
        });
        return $timer;
    }

}
