<?php

namespace Mix\Console\CommandLine;

/**
 * Class Flag
 * @package Mix\Console\CommandLine
 * @author liu,jian <coder.keda@gmail.com>
 */
class Flag
{

    /**
     * 命令行选项
     * @var array
     */
    protected static $_options = [];

    /**
     * 初始化
     */
    public static function init()
    {
        // 解析全部选项
        $start = 1;
        if (Argument::command() == '') {
            $start = 0;
        }
        $argv = $GLOBALS['argv'];
        $tmp  = [];
        foreach ($argv as $key => $item) {
            if ($key <= $start) {
                continue;
            }
            $name  = $item;
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
        self::$_options = $tmp;
    }

    /**
     * 获取布尔值
     * @param $name
     * @param bool $default
     * @return bool
     */
    public static function bool($name, bool $default = false)
    {
        $names = [$name];
        if (is_array($name)) {
            $names = $name;
        }
        $flags = [];
        foreach ($names as $item) {
            if (strlen($item) == 1) {
                $flags[] = "-{$item}";
            } else {
                $flags[] = "--{$item}";
            }
        }
        foreach (static::options() as $key => $value) {
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
     * @param string|array $name
     * @param string $default
     * @return string
     */
    public static function string($name, string $default = '')
    {
        $names = [$name];
        if (is_array($name)) {
            $names = $name;
        }
        $flags = [];
        foreach ($names as $item) {
            if (strlen($item) == 1) {
                $flags[] = "-{$item}";
            } else {
                $flags[] = "--{$item}";
            }
        }
        foreach (static::options() as $key => $value) {
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
     * @param $name
     * @param int $default
     * @return int
     */
    public static function int($name, int $default = 0)
    {
        $value = static::string($name, '');
        if ($value === '') {
            return $default;
        }
        return (int)$value;
    }

    /**
     * 获取浮点数
     * @param $name
     * @param float $default
     * @return float
     */
    public static function float($name, float $default = 0)
    {
        $value = static::string($name, '');
        if ($value === '') {
            return $default;
        }
        return (float)$value;
    }

    /**
     * 全部命令行选项
     * @return array
     */
    public static function options()
    {
        return self::$_options;
    }

    /**
     * 全部命令行值
     * @return array
     */
    public static function values()
    {
        $options = static::options();
        $values  = [];
        foreach ($options as $flag => $value) {
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
