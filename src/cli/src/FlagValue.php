<?php

namespace Mix\Cli;

/**
 * Class FlagValue
 * @package Mix\Cli
 */
class FlagValue
{

    /**
     * @var string
     */
    protected $value;

    /**
     * @var bool
     */
    protected $exist;

    /**
     * FlagValue constructor.
     * @param string $value
     * @param bool $exist
     */
    public function __construct(string $value = '', bool $exist = false)
    {
        $this->value = $value;
        $this->exist = $exist;
    }

    /**
     * @param string $default
     * @return string
     */
    public function string(string $default = ''): string
    {
        if ($this->value === '') {
            return $default;
        }
        return $this->value;
    }

    /**
     * @param bool $default
     * @return bool
     */
    public function bool(bool $default = false): bool
    {
        if (!$this->exist) {
            return $default;
        }
        switch ($this->value) {
            case 'false':
                return false;
            default:
                return true;
        }
    }

    /**
     * @param int $default
     * @return int
     */
    public function int(int $default = 0): int
    {
        $value = static::string('');
        if ($value === '') {
            return $default;
        }
        return (int)$value;
    }

    /**
     * @param float $default
     * @return float
     */
    public function float(float $default = 0): float
    {
        $value = static::string('');
        if ($value === '') {
            return $default;
        }
        return (float)$value;
    }

}
