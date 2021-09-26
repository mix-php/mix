<?php

namespace App\Container;

/**
 * Class Upgrader
 * @package App\Container
 */
class Upgrader
{

    /**
     * @var \Mix\WebSocket\Upgrader
     */
    private static $instance;

    public static function init(): void
    {
        self::$instance = new \Mix\WebSocket\Upgrader();
    }

    /**
     * @return \Mix\WebSocket\Upgrader
     */
    public static function instance(): \Mix\WebSocket\Upgrader
    {
        if (!isset(self::$instance)) {
            static::init();
        }
        return self::$instance;
    }

}