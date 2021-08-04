<?php

namespace Mix\Cli;

/**
 * Class Arguments
 * @package Mix\Cli
 */
class Arguments
{

    /**
     * @var array
     */
    protected $values = [];

    /**
     * Arguments constructor.
     * @param array $values
     */
    public function __construct(array $values)
    {
        $this->values = $values;
    }

    /**
     * @return array
     */
    public function array(): array
    {
        return $this->values;
    }

    /**
     * @return FlagValue[]
     */
    public function values(): array
    {
        $values = [];
        foreach ($this->values as $value) {
            $values[] = new FlagValue($value, true);
        }
        return $values;
    }

    /**
     * @return FlagValue
     */
    public function first(): FlagValue
    {
        if (count($this->values) == 0) {
            return new FlagValue();
        }
        return new FlagValue($this->values[0], true);
    }

}
