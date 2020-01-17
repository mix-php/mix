<?php

namespace Mix\Concurrent;

/**
 * Class Coroutine
 * @package Mix\Concurrent
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
                $isMix = class_exists(\Mix::class);
                // 错误处理
                if (!$isMix) {
                    throw $e;
                }
                // Mix错误处理
                /** @var \Mix\Console\Error $error */
                $error = \Mix::$app->context->get('error');
                $error->handleException($e);
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
                $isMix = class_exists(\Mix::class);
                // 错误处理
                if (!$isMix) {
                    throw $e;
                }
                // Mix错误处理
                /** @var \Mix\Console\Error $error */
                $error = \Mix::$app->context->get('error');
                $error->handleException($e);
            }
        });
    }

}
