<?php

namespace Mix\Etcd\Node;

/**
 * Class Node
 * @package Mix\Etcd\Node
 */
class Node
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
     * @var string[]
     */
    public $services = [];

    /**
     * Node constructor.
     * @param string $id
     * @param string $name
     * @param string $address
     */
    public function __construct(string $id, string $name, string $address)
    {
        $this->id      = $id;
        $this->name    = $name;
        $this->address = $address;
    }

}
