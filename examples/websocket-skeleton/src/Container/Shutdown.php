<?php

namespace App\Container;

/**
 * Class Shutdown
 * @package App\Container
 */
class Shutdown
{

    /**
     * @var \Closure[]
     */
    private static $onShutdown = [];

    /**
     * @param \Closure $func
     */
    public static function register(\Closure $func): void
    {
        static::$onShutdown[] = $func;
    }

    /**
     * @return void
     */
    public static function trigger(): void
    {
        foreach (static::$onShutdown as $func) {
            $func();
        }
    }

}
