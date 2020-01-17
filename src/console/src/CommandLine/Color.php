<?php

namespace Mix\Console\CommandLine;

/**
 * Class Color
 * @package Mix\Console
 */
class Color
{

    // 式样
    const RESET = "\033[0m";
    const BOLD = "\033[1m";
    const FAINT = "\033[2m";
    const ITALIC = "\033[3m";
    const UNDERLINE = "\033[4m";
    const BLINK_SLOW = "\033[5m";
    const BLINK_RAPID = "\033[6m";
    const REVERSE_VIDEO = "\033[7m";
    const CONCEALED = "\033[8m";
    const CROSSED_OUT = "\033[9m";
    // 前景色
    const FG_BLACK = "\033[30m";
    const FG_RED = "\033[31m";
    const FG_GREEN = "\033[32m";
    const FG_YELLOW = "\033[33m";
    const FG_BLUE = "\033[34m";
    const FG_MAGENTA = "\033[35m";
    const FG_CYAN = "\033[36m";
    const FG_WHITE = "\033[37m";
    // 背景色
    const BG_BLACK = "\033[40m";
    const BG_RED = "\033[41m";
    const BG_GREEN = "\033[42m";
    const BG_YELLOW = "\033[43m";
    const BG_BLUE = "\033[44m";
    const BG_MAGENTA = "\033[45m";
    const BG_CYAN = "\033[46m";
    const BG_WHITE = "\033[47m";
    // 前景色高亮
    const FG_HI_BLACK = "\033[90m";
    const FG_HI_RED = "\033[91m";
    const FG_HI_GREEN = "\033[92m";
    const FG_HI_YELLOW = "\033[93m";
    const FG_HI_BLUE = "\033[94m";
    const FG_HI_MAGENTA = "\033[95m";
    const FG_HI_CYAN = "\033[96m";
    const FG_HI_WHITE = "\033[97m";
    // 背景色高亮
    const BG_HI_BLACK = "\033[100m";
    const BG_HI_RED = "\033[101m";
    const BG_HI_GREEN = "\033[102m";
    const BG_HI_YELLOW = "\033[103m";
    const BG_HI_BLUE = "\033[104m";
    const BG_HI_MAGENTA = "\033[105m";
    const BG_HI_CYAN = "\033[106m";
    const BG_HI_WHITE = "\033[107m";

    /**
     * 设置的属性
     * @var array
     */
    protected $_attributes = [];

    /**
     * 使用静态方法创建实例
     * @param mixed ...$args
     * @return $this
     */
    public static function new(...$args)
    {
        return new static(...$args);
    }

    /**
     * 构造
     * Color constructor.
     * @param mixed ...$attributes
     */
    public function __construct(...$attributes)
    {
        call_user_func_array([$this, 'add'], $attributes);
    }

    /**
     * 新增属性
     * @param mixed ...$attributes
     * @return $this
     */
    public function add(...$attributes)
    {
        $this->_attributes = array_merge($this->_attributes, $attributes);
        return $this;
    }

    /**
     * 打印
     * @param $strings
     */
    public function print($strings)
    {
        echo $this->sprint($strings);
    }

    /**
     * 格式化打印
     * @param $format
     * @param mixed ...$args
     */
    public function printf($format, ...$args)
    {
        echo call_user_func_array([$this, 'sprintf'], array_merge([$format], $args));
    }

    /**
     * 打印并换行
     * @param $strings
     */
    public function println($strings)
    {
        echo $this->sprintln($strings);
    }

    /**
     * 返回变量
     * @param $strings
     * @return string
     */
    public function sprint($strings)
    {
        if (empty($this->_attributes)) {
            return $strings;
        }
        return implode('', $this->_attributes) . $strings . self::RESET;
    }

    /**
     * 格式化后返回
     * @param $format
     * @param mixed ...$args
     * @return string
     */
    public function sprintf($format, ...$args)
    {
        $strings = call_user_func_array('sprintf', array_merge([$format], $args));
        return $this->sprint($strings);
    }

    /**
     * 格式化换行后返回
     * @param $strings
     * @return string
     */
    public function sprintln($strings)
    {
        $strings = $this->sprint($strings);
        return $strings . PHP_EOL;
    }

}
