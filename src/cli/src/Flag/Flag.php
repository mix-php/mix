<?php

namespace Mix\Cli\Flag;

use Mix\Cli\Argument\Arguments;

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

    public static function parse(): void
    {
        $start = 1;
        if (Arguments::command() == '') {
            $start = 0;
        }
        $argv = $GLOBALS['argv'];
        $tmp = [];
        foreach ($argv as $key => $item) {
            if ($key <= $start) {
                continue;
            }
            $name = $item;
            $value = '';
            if (strpos($name, '=') !== false) {
                list($name) = explode('=', $item);
                $value = ltrim(strstr($item, "="), "=");
            }
            if (substr($name, 0, 2) == '--' || substr($name, 0, 1) == '-') {
                // 无值参数处理
                if (substr($name, 0, 1) == '-' && $value === '' && isset($argv[$key + 1]) && substr($argv[$key + 1], 0, 1) != '-') {
                    $next = $argv[$key + 1];
                    if (preg_match('/^[\S\s]+$/i', $next)) {
                        $value = $next;
                    }
                }
            } else {
                $name = '';
            }
            if ($name !== '') {
                $tmp[$name] = $value;
            }
        }
        self::$options = $tmp;
    }

    /**
     * @param string ...$names
     * @return FlagValue
     */
    public static function match(string ...$names): FlagValue
    {
        return new FlagValue(...$names);
    }

    /**
     * 全部命令行选项
     * @return array
     */
    public static function options(): array
    {
        return self::$options;
    }

    /**
     * 全部命令行值
     * @return array
     */
    public static function values(): array
    {
        $values = [];
        foreach (static::options() as $flag => $value) {
            if (substr($flag, 0, 2) == '--') {
                $values[substr($flag, 2)] = $value;
                continue;
            }
            if (substr($flag, 0, 1) == '-') {
                $values[substr($flag, 1)] = $value;
                continue;
            }
        }
        return $values;
    }

}
