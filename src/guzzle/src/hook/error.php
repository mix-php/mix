<?php

namespace GuzzleHttp\Handler {

    /**
     * 重写写系统方法，使其失效
     */
    if (!function_exists('Monolog\Handler\set_error_handler')) {
        function set_error_handler($call)
        {
        }
    }

    /**
     * 重写系统方法，使其失效
     */
    if (!function_exists('Monolog\Handler\restore_error_handler')) {
        function restore_error_handler()
        {
        }
    }

    /**
     * 重写系统方法
     */
    if (!function_exists('Monolog\Handler\fopen')) {
        function fopen($filename, $mode, $use_include_path = null, $context = null)
        {
            $fp = @\fopen($filename, $mode, $use_include_path, $context);
            if ($fp === false) {
                throw new \RuntimeException(sprintf('fopen(%s): failed to open stream', $filename));
            }
            return $fp;
        }
    }

}

namespace GuzzleHttp\Psr7 {

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
            $fp = @\fopen($filename, $mode, $use_include_path, $context);
            if ($fp === false) {
                throw new \RuntimeException(sprintf('fopen(%s): failed to open stream', $filename));
            }
            return $fp;
        }
    }

}
