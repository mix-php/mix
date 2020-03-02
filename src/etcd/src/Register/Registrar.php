<?php

namespace Mix\Etcd\Register;

use Etcd\Client;
use Mix\Concurrent\Timer;
use Mix\Etcd\Node\Node;
use Mix\Etcd\Service\ServiceBundle;
use Ramsey\Uuid\UuidFactory;

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
        $node    = new Node((new UuidFactory())->uuid4()->toString(), gethostname(), current(swoole_get_local_ip()));
        foreach ($bundle->items() as $service) {
            $node->services[] = [
                'id'   => $service->id,
                'name' => $service->name,
            ];
            $service->node    = [
                'id'   => $node->id,
                'name' => $node->name,
            ];
            $client->put(sprintf('/service/%s/%s', $service->name, $service->id), json_encode($service), ['lease' => $leaseID]);
        }
        $client->put(sprintf('/node/%s/%s', $node->name, $node->id), json_encode($node), ['lease' => $leaseID]);
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
            $this->client->keepAlive($this->leaseID);
        });
        return $timer;
    }

}
