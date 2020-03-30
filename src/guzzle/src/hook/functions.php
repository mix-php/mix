<?php

namespace GuzzleHttp;

use GuzzleHttp\Handler\StreamHandler;

/**
 * 重写该方法，使其固定为 StreamHandler
 * Chooses and creates a default handler to use based on the environment.
 *
 * The returned handler is not wrapped by any default middlewares.
 *
 * @return callable Returns the best handler for the given system.
 * @throws \RuntimeException if no viable Handler is available.
 */
function choose_handler()
{
    $handler = new StreamHandler();
    return $handler;
}

/**
 * 加载剩余的方法
 */
$class = 'GuzzleHttp\Client';
$ver   = $class::VERSION;
$ver   = explode('.', $ver);
array_pop($ver);
$ver = implode('.', $ver);
$dir = "{$ver}";
if (!is_dir(__DIR__ . "/{$dir}")) {
    return;
}
require __DIR__ . "/{$dir}/functions.php";
