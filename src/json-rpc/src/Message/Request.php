<?php

namespace Mix\JsonRpc\Message;

/**
 * Class Request
 * @package Mix\JsonRpc\Message
 */
class Request implements \JsonSerializable
{

    /**
     * @var \ArrayObject
     */
    public $context;

    /**
     * @var string
     */
    public $jsonrpc;

    /**
     * @var string
     */
    public $method;

    /**
     * @var array|object
     */
    public $params;

    /**
     * @var int
     */
    public $id;

    /**
     * Request constructor.
     */
    public function __construct()
    {
        $this->context = new \ArrayObject();
    }

    /**
     * Json serialize
     * @return array
     */
    public function jsonSerialize()
    {
        $data = [];
        foreach ($this as $key => $val) {
            if ($key == 'context') {
                continue;
            }
            if ($key != 'id' && is_null($val)) {
                continue;
            }
            $data[$key] = $val;
        }
        return $data;
    }

}
