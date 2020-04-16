<?php

namespace Mix\Micro\Register;

/**
 * Interface NodeFactoryInterface
 * @package Mix\Micro\Register
 */
interface NodeFactoryInterface
{

    /**
     * Create node
     * @param string $name
     * @param string $address
     * @return NodeInterface
     */
    public function createNode(string $name, string $address): NodeInterface;

}
