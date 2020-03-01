<?php

namespace Mix\Etcd\Service;

use Ramsey\Uuid\UuidFactory;

/**
 * Class ServiceManager
 * @package Mix\Etcd\Service
 */
class Service
{

    /**
     * @var string
     */
    public $id = '';

    /**
     * @var string
     */
    public $name = '';

    /**
     * @var string
     */
    public $address = '';

    /**
     * @var int
     */
    public $port = 0;

    /**
     * Service constructor.
     * @param string $id
     * @param string $name
     * @param string $address
     * @param int $port
     * @throws \Exception
     */
    public function __construct(string $id, string $name, string $address, int $port)
    {
        $this->id      = $id;
        $this->name    = $name;
        $this->address = $address;
        $this->port    = $port;
    }

}
