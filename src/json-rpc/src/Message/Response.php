<?php

namespace Mix\JsonRpc\Message;

/**
 * Class Response
 * @package Mix\JsonRpc\Message
 */
class Response implements \JsonSerializable
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
     * @var array|object
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
     * @var array|object
     */
    public $params;

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
