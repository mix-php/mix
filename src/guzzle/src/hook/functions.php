<?php

namespace GuzzleHttp\Psr7;

/**
 * 重写写系统方法，使其失效
 */
if (!function_exists('GuzzleHttp\Psr7\set_error_handler')) {
    function set_error_handler($call)
    {
    }
}

/**
 * 重写系统方法，使其失效
 */
if (!function_exists('GuzzleHttp\Psr7\restore_error_handler')) {
    function restore_error_handler()
    {
    }
}

/**
 * 重写系统方法
 */
if (!function_exists('GuzzleHttp\Psr7\fopen')) {
    function fopen($filename, $mode, $use_include_path = null, $context = null)
    {
        switch (func_num_args()) {
            case 2:
                $fp = @\fopen($filename, $mode);
                break;
            case 3:
                $fp = @\fopen($filename, $mode, $use_include_path);
                break;
            default:
                $fp = @\fopen($filename, $mode, $use_include_path, $context);
        }
        if ($fp === false) {
            throw new \RuntimeException(sprintf('Unable to open %s using mode %s', $filename, $mode));
        }
        return $fp;
    }
}
