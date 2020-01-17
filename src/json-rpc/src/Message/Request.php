<?php

namespace Mix\JsonRpc\Message;

/**
 * Class Request
 * @package Mix\JsonRpc\Message
 */
class Request
{

    /**
     * @var string
     */
    public $jsonrpc;

    /**
     * @var string
     */
    public $method;

    /**
     * @var array
     */
    public $params;

    /**
     * @var int
     */
    public $id;

}
