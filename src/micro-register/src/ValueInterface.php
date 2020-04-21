<?php

namespace Mix\Micro\Register;

/**
 * Interface ValueInterface
 * @package Mix\Micro\Register
 */
interface ValueInterface extends \JsonSerializable
{

    /**
     * Get name
     * @return string
     */
    public function getName(): string;

    /**
     * Get type
     * @return string
     */
    public function getType(): string;

    /**
     * Get values
     * @return ValueInterface[]|null
     */
    public function getValues();

    /**
     * Set add value
     * @param ValueInterface $value
     */
    public function withValue(ValueInterface $value);

}
