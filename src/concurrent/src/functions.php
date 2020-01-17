<?php

/**
 * 助手函数
 * @author liu,jian <coder.keda@gmail.com>
 */

if (!function_exists('xgo')) {
    // 创建协程
    function xgo($function, ...$params)
    {
        \Mix\Concurrent\Coroutine::create($function, ...$params);
    }
}

if (!function_exists('xdefer')) {
    // 创建延迟执行
    function xdefer($function)
    {
        return \Mix\Concurrent\Coroutine::defer($function);
    }
}
