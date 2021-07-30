<?php

namespace Mix\Cli\Flag;

/**
 * Class FlagValue
 * @package Mix\Cli\Flag
 */
class FlagValue
{

    protected $names = [];

    /**
     * FlagValue constructor.
     * @param string ...$names
     */
    public function __construct(string ...$names)
    {
        $this->names = $names;
    }

    /**
     * 获取布尔值
     * @param bool $default
     * @return bool
     */
    public function bool(bool $default = false): bool
    {
        $flags = [];
        foreach ($this->names as $item) {
            if (strlen($item) == 1) {
                $flags[] = "-{$item}";
            } else {
                $flags[] = "--{$item}";
            }
        }
        foreach (Flag::options() as $key => $value) {
            if (in_array($key, $flags, true)) {
                if ($value === 'false') {
                    return false;
                }
                return true;
            }
        }
        return $default;
    }

    /**
     * 获取字符值
     * @param string $default
     * @return string
     */
    public function string(string $default = ''): string
    {
        $flags = [];
        foreach ($this->names as $item) {
            if (strlen($item) == 1) {
                $flags[] = "-{$item}";
            } else {
                $flags[] = "--{$item}";
            }
        }
        foreach (Flag::options() as $key => $value) {
            if (in_array($key, $flags, true)) {
                if ($value === '') {
                    return $default;
                }
                return $value;
            }
        }
        return $default;
    }

    /**
     * 获取整数
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
     * 获取浮点数
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
