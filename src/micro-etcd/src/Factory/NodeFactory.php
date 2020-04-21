<?php

namespace Mix\Micro\Etcd\Factory;

use Mix\Micro\Etcd\Service\Node;
use Mix\Micro\Register\Helper\ServiceHelper;
use Mix\Micro\Register\NodeFactoryInterface;
use Mix\Micro\Register\NodeInterface;

/**
 * Class NodeFactory
 * @package Mix\Micro\Etcd\Factory
 */
class NodeFactory implements NodeFactoryInterface
{

    /**
     * Create node
     * @param string $name
     * @param string $address
     * @return NodeInterface
     */
    public function createNode(string $name, string $address): NodeInterface
    {
        $id = ServiceHelper::uuid();
        return new Node(sprintf('%s-%s', $name, $id), $address);
    }

}
