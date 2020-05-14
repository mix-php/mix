<?php

/**
 * 助手函数
 * @author liu,jian <coder.keda@gmail.com>
 */

if (!function_exists('app')) {
    /**
     * 返回App实例
     * @return \Mix\Console\Application
     */
    function app()
    {
        return \Mix::$app;
    }
}

if (!function_exists('context')) {
    /**
     * 返回Context实例
     * @return \Mix\Bean\ApplicationContext
     */
    function context()
    {
        return \Mix::$app->context;
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
