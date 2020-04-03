<?php

namespace Mix\JsonRpc\Message;

/**
 * Class Request
 * @package Mix\JsonRpc\Message
 */
class Request implements \JsonSerializable
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
     * @var array
     */
    public $metadata;

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
            if ($key != 'id' && is_null($val)) {
                continue;
            }
            $data[$key] = $val;
        }
        return $data;
    }

}
