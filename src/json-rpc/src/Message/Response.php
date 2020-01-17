<?php

namespace Mix\JsonRpc\Message;

/**
 * Class Response
 * @package Mix\JsonRpc\Message
 */
class Response
{

    /**
     * @var string
     */
    public $jsonrpc;

    /**
     * @var int|null
     */
    public $id;

    /**
     * @var array
     */
    public $result;

    /**
     * @var null|Error
     */
    public $error;

    /**
     * @var string
     */
    public $method;

    /**
     * @var array
     */
    public $params;

}
