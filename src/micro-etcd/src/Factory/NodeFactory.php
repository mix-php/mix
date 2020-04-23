<?php

namespace Mix\Micro\Etcd\Factory;

use Mix\Micro\Etcd\Service\Node;
use Mix\Micro\Register\Helper\ServiceHelper;

/**
 * Class NodeFactory
 * @package Mix\Micro\Etcd\Factory
 */
class NodeFactory 
{

    /**
     * Create node
     * @param string $name
     * @param string $address
     * @return Node
     */
    public function createNode(string $name, string $address): Node
    {
        $id = ServiceHelper::uuid();
        return new Node(sprintf('%s-%s', $name, $id), $address);
    }

}
