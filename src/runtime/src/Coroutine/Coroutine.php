<?php

namespace Mix\Coroutine;

/**
 * Class Coroutine
 * @package Mix\Coroutine
 * @author liu,jian <coder.keda@gmail.com>
 */
class Coroutine
{

    /**
     * 创建协程
     * @param callable $callback
     * @param mixed ...$params
     * @return int|false
     */
    public static function create(callable $callback, ...$params)
    {
        return \Swoole\Coroutine::create(function () use ($callback, $params) {
            try {
                // 执行闭包
                call_user_func_array($callback, $params);
            } catch (\Throwable $e) {
                // 错误处理
                if (!class_exists(\Mix::class)) {
                    throw $e;
                }
                \Mix::$app->error->handleException($e);
            }
        });
    }

    /**
     * 延迟执行
     * @param callable $callback
     * @return void
     */
    public static function defer(callable $callback)
    {
        \Swoole\Coroutine::defer(function () use ($callback) {
            try {
                // 执行闭包
                call_user_func($callback);
            } catch (\Throwable $e) {
                // 错误处理
                if (!class_exists(\Mix::class)) {
                    throw $e;
                }
                \Mix::$app->error->handleException($e);
            }
        });
    }

}
