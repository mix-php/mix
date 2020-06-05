<?php

/**
 * 助手函数
 * @author liu,jian <coder.keda@gmail.com>
 */


if (!function_exists('xgo')) {
    /**
     * 创建协程
     * @param $function
     * @param mixed ...$params
     */
    function xgo($function, ...$params)
    {
        \Mix\Coroutine\Coroutine::create($function, ...$params);
    }
}

if (!function_exists('xdefer')) {
    /**
     * 创建延迟执行
     * @param $function
     */
    function xdefer($function)
    {
        return \Mix\Coroutine\Coroutine::defer($function);
    }
}

if (!function_exists('println')) {
    /**
     * 输出字符串并换行
     * @param mixed ...$values
     */
    function println(...$values)
    {
        $slice = [];
        foreach ($values as $value) {
            if (is_scalar($value)) {
                $slice[] = $value;
            } else {
                $slice[] = json_encode($value);
            }
        }
        echo implode(' ', $slice) . PHP_EOL;
    }
}
