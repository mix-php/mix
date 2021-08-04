<?php

namespace Mix\Cli\Flag;

use Mix\Cli\Argument\ArgumentVector;

/**
 * Class Flag
 * @package Mix\Cli\Flag
 */
class Flag
{

    /**
     * @var array
     */
    protected static $options = [];

    /**
     * @var array
     */
    protected static $arguments = [];

    /**
     * parse
     */
    public static function parse(): void
    {
        $start = 1;
        if (ArgumentVector::command() == '') {
            $start = 0;
        }
        $opts = $args = [];
        $ignore = '';
        foreach ($GLOBALS['argv'] as $key => $val) {
            if ($key <= $start) {
                continue;
            }
            $name = $val;
            $value = '';
            if (strpos($name, '=') !== false) {
                list($name) = explode('=', $val);
                $value = ltrim(strstr($val, "="), "=");
            }
            if (substr($name, 0, 1) == '-' || substr($name, 0, 2) == '--') {
                if (substr($name, 0, 1) == '-' && $value === '' && isset($argv[$key + 1]) && substr($argv[$key + 1], 0, 1) != '-') {
                    $next = $argv[$key + 1];
                    if (preg_match('/^[\S\s]+$/i', $next)) {
                        $value = $next;
                        $ignore = $next;
                    }
                }
            } else {
                $name = '';
                if ($val != $ignore) {
                    $args[] = $val;
                }
            }
            if ($name !== '') {
                $opts[$name] = $value;
            }
        }
        static::$options = $opts;
        static::$arguments = $args;
    }

    /**
     * @param string ...$names
     * @return FlagValue
     */
    public static function match(string ...$names): FlagValue
    {
        $find = function (string $key) {
            if (strlen($key) == 1) {
                $flag = "-{$key}";
            } else {
                $flag = "--{$key}";
            }
            return static::options()[$flag] ?? null;
        };
        foreach ($names as $name) {
            $v = $find($name);
            if (!is_null($v)) {
                return new FlagValue($v, true);
            }
        }
        return new FlagValue();
    }

    /**
     * @return array ['--foo' => 'bar']
     */
    public static function options(): array
    {
        return static::$options;
    }

    /**
     * @return Arguments
     */
    public static function arguments(): Arguments
    {
        return new Arguments(static::$arguments);
    }

}
