<?php

namespace Mix\Micro\Etcd\Service;

/**
 * Class Response
 * @package Mix\Micro\Etcd\Service
 */
class Response implements \JsonSerializable
{

    /**
     * @var string
     */
    protected $name = '';

    /**
     * @var string
     */
    protected $type = '';

    /**
     * @var Value[]
     */
    protected $values;

    /**
     * Request constructor.
     * @param string $name
     * @param string $type
     */
    public function __construct(string $name, string $type)
    {
        $this->name = $name;
        $this->type = $type;
    }

    /**
     * Get name
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get type
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Get values
     * @return Value[]|null
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * Set add value
     * @param Value $value
     */
    public function withValue(Value $value)
    {
        $this->values[] = $value;
    }

    /**
     * Json serialize
     * @return array
     */
    public function jsonSerialize()
    {
        $data = [];
        foreach ($this as $key => $val) {
            $data[$key] = $val;
        }
        return $data;
    }

}
