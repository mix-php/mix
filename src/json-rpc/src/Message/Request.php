<?php

namespace Mix\JsonRpc\Message;

use Mix\Context\Context;

/**
 * Class Request
 * @package Mix\JsonRpc\Message
 */
class Request implements \JsonSerializable
{

    /**
     * @var Context
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
     * @var array
     */
    public $params;

    /**
     * @var int
     */
    public $id;

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
